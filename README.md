# Pokepedia [![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)

Pokemon stats based upon [pokeAPI](http://pokeapi.co/) 
 
## Technical stack

* **Symfony3**
* **Redis**
* **[PokePHP](https://github.com/danrovito/pokephp)** PokeAPI PHP wrapper

## Installation

### Prerequisites

* [Symfony3 requirements](http://symfony.com/doc/current/reference/requirements.html)
* Redis

### Define Pokedex

Used Pokedex is defined in Pokepedia parameters: 
```
pokedex:
    id: 2
```
Refers to [PokeAPI Pokedex doc](http://pokeapi.co/docsv2/#pokedexes) to find the Pokedex ID matching your needs.

### Init / Update Redis DB
Redis is used to store some basic data in order to speed-up operations and reduce API calls.

Two indexes are used:
1) **Pokemons** will store Pokemon names and will be mainly used for searches
2) **Stats** will gather Pokemon base stats (Speed, HP, etc.) and group them by [Pokemon type](http://bulbapedia.bulbagarden.net/wiki/Type).

Initialization, update or truncate indexes is done with the following Symfony console command:
```
$ bin/console pokedex:update [--flush]
```

**Note:** 
* option `--flush` will flush Redis indexes (after user confirmation).
* Redis indexes are updated once Pokemon retrieval is over. Script interruption during the Pokemon fetch will not affect indexes (unless you flushed them).
* When changing Pokedex ID, its better to flush indexes.

Once indexes operations are over, general type stats will be displayed.

```
 ---------- ---- ------ ------- ------- ------ ------ ------ 
  STAT       #    SPD    S-DEF   S-ATT   DEF    ATT    HP    
 ---------- ---- ------ ------- ------- ------ ------ ------ 
  poison     35   2148   2268    2410    2070   2341   2103  
  grass      16   785    1050    1305    1082   1124   980   
  fire       12   1008   920     1015    751    1007   766   
  bug        12   685    665     570     685    765    665   
  water      32   2121   2099    2140    2590   2262   2002  
  normal     22   1525   1317    1128    1178   1490   1731  
  flying     19   1619   1339    1314    1262   1598   1297  
  electric   9    890    660     820     582    558    490   
  ground     14   796    750     635     1359   1189   846   
  fairy      5    250    350     385     251    275    460   
  fighting   8    533    605     385     522    815    535   
  psychic    14   1094   1145    1370    920    881    976   
  rock       11   590    575     620     1205   955    670   
  steel      2    115    125     215     165    95     75    
  ice        5    380    455     450     475    385    425   
  ghost      3    285    165     345     135    150    135   
  dragon     3    200    220     220     205    282    193   
 ---------- ---- ------ ------- ------- ------ ------ ------ 
```

Each stat represent the addition of this stat for each Pokemon having this type. Second column represent the total number of Pokemon for the related type (some Pokemon can cumulate several types).

Average stats can easily be obtained. By example, we have 19 flying Pokemon, average speed (SPD) for Pokemon of this type will be 1619 / 19 = **85.2**
