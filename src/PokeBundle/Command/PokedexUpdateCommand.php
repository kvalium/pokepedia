<?php

namespace PokeBundle\Command;

use PokePHP\PokeApi;
use Predis\Client as PredisClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PokedexUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pokedex:update')
            ->setDescription('Update the pokedex database')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'Flush local pokedex');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $api = new PokeApi;
        /** @var PredisClient $redis */
        $redis = $this->getContainer()->get('snc_redis.default');

        $io->title('Pokepedia Pokedex update command');

        if ($input->getOption('flush')) {
            $io->warning('Flush option detected');
            if ($io->confirm('Would you really want to flush all data on the local pokedex?', false)) {
                if ($redis->flushdb()) {
                    $io->success('Pokedex is now empty.');
                }
            }
        }

        // @todo avoid static pokedex ID
        $pokedex = json_decode($api->pokedex(1));
        $nbRemotePokedex = count($pokedex->pokemon_entries);

        $io->writeln($redis->dbsize().'/'.$nbRemotePokedex.' entries in the local pokedex');

        if ($nbRemotePokedex === $redis->dbsize()) {
            $io->success('Pokedex is up to date :)');
        }
        foreach ($pokedex->pokemon_entries as $pokemon_entry) {
            $pokemonName = $pokemon_entry->pokemon_species->name;
            if ($redis->setnx($pokemon_entry->pokemon_species->name, $pokemon_entry->pokemon_species->name)) {
                $output->writeln('* adding pokemon: '.$pokemonName);
            }
        };

        $output->writeln('Command result.');
    }

}
