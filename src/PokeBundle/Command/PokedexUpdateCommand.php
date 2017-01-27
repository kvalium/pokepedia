<?php

namespace PokeBundle\Command;

use PokeBundle\Services\PokeService;
use PokeBundle\Utils\Pokemon;
use Predis\Client as PredisClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PokedexUpdateCommand extends ContainerAwareCommand
{
    private $typeStats;

    /** @var PredisClient $redis */
    private $redis;
    /** @var  SymfonyStyle $io */
    private $io;

    protected function configure()
    {
        $this
            ->setName('pokedex:update')
            ->setDescription('Update the pokedex database')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'Flush local pokedex');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->redis = $this->getContainer()->get('snc_redis.default');

        /** @var PokeService $pokeService */
        $pokeService = $this->getContainer()->get('poke.service');

        $this->io->title('Pokepedia Pokedex update command');

        if ($input->getOption('flush')) {
            $this->flushRedis();
        }

        $this->io->section('Fetching remote Pokedex');

        $pokedexID = $this->getContainer()->getParameter('pokedex')['id'];
        $pokedex = $pokeService->getPokedex($pokedexID);
        $nbRemotePokedex = count($pokedex->pokemon_entries);

        $this->io->writeln($this->redis->dbsize() . '/' . $nbRemotePokedex . ' entries in the local pokedex');

        $this->io->section('Synchronizing local Pokedex');

        if ($nbRemotePokedex === $this->redis->dbsize()) {
            $this->io->success('Pokedex is up to date :)');
        }

        // retrieve current stats from redis
        if (!$input->getOption('flush')) {
            $this->loadTypeStats();
        }

        // Fetching remote pokedex then store Pokemon names
        $toIndex = array();
        $this->redis->select($this->getContainer()->getParameter('redis.databases')['pokemons']);
        foreach ($pokedex->pokemon_entries as $i => $pokemon_entry) {
            $pokemonName = $pokemon_entry->pokemon_species->name;
            if (!$this->redis->exists($pokemonName)) {
                $output->writeln('[' . ($i + 1) . '/' . $nbRemotePokedex . '] fetching pokemon: ' . $pokemonName);
                /** @var Pokemon $pokemon */
                $pokemon = $pokeService->getPokemonData($pokemon_entry->entry_number);
                $toIndex[] = $pokemon->getName();
                $this->gatherTypeStats($pokemon);
            }
        };

        // Append Redis now with fetched names to prevent script abortion during the previous part
        $this->storePokemons($toIndex);
        // same with avg stats
        $this->storeTypeStates();

        $this->redis->save();
        $output->writeln('Done.');
    }

    /**
     * Flush Redis when option --flush is set
     */
    private function flushRedis()
    {
        $this->io->warning('Flush option detected');
        if ($this->io->confirm('Would you really want to flush all data on the local pokedex?', false)) {
            if ($this->redis->flushall()) {
                $this->io->success('Pokedex is now empty.');
            }
        }
    }

    /**
     * Load stored type stats from Redis and populate the typeStats array
     */
    protected function loadTypeStats()
    {
        $this->typeStats = array();
        $this->redis->select($this->getContainer()->getParameter('redis.databases')['types']);
        foreach ($this->redis->keys('*') as $typeKey) {
            foreach ($this->redis->hgetall($typeKey) as $stat => $value) {
                $this->typeStats[$typeKey][$stat] = $value;
            }
        }
    }

    /**
     * Gather pokemon stats and group them by (pokemon) type
     *
     * @param Pokemon $pokemon
     */
    protected function gatherTypeStats(Pokemon $pokemon)
    {
        if (!$this->typeStats) {
            $this->typeStats = array();
        }

        foreach ($pokemon->getTypes() as $type) {
            if (!isset($this->typeStats[$type])) {
                $this->typeStats[$type] = array('total' => 0);
            }
            $this->typeStats[$type]['total']++;
            foreach ($pokemon->getStats() as $stat => $value) {
                if (!isset($this->typeStats[$type][$stat])) {
                    $this->typeStats[$type][$stat] = $value;
                    continue;
                }
                $this->typeStats[$type][$stat] += $value;
            }
        }
    }

    /**
     * Store Pokemon on Redis with following struct:
     * @key Pokemon Name
     * @value 0
     * value will represent a local counter
     *
     * @param $toIndex
     * @return bool
     */
    protected function storePokemons($toIndex)
    {
        if (!$toIndex) {
            return false;
        }
        dump($toIndex);
        $this->io->newLine();
        $this->io->section('Appending Redis');
        $this->redis->select($this->getContainer()->getParameter('redis.databases')['pokemons']);
        $this->io->progressStart(count($toIndex));
        foreach ($toIndex as $pokemonName) {
            $this->redis->setnx($pokemonName, 0);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return true;
    }

    /**
     * Store gathered type stats on Redis, using an hash
     * @key stat name
     * @value array containing stats
     */
    protected function storeTypeStates()
    {
        $this->redis->select($this->getContainer()->getParameter('redis.databases')['types']);

        $tableOutput = array();
        foreach ($this->typeStats as $statName => $stats) {
            $this->redis->hmset($statName, $stats);
            array_unshift($stats, $statName);
            $tableOutput[] = $stats;
        }

        $this->io->table(
            array('STAT', '#', 'SPD', 'S-DEF', 'S-ATT', 'DEF', 'ATT', 'HP'),
            $tableOutput
        );

    }

}
