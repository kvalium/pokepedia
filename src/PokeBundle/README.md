# PokeBundle

This bundle provides various tools based upon the [pokeAPI](http://pokeapi.co/) and exposes a RESTful API.

## Pokepedia API

Please read the related [API documentation](Resources/doc/api.md).

## Installation

### Prerequisites

* [Symfony3 requirements](http://symfony.com/doc/current/reference/requirements.html)
* Redis
* bower

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
* When changing Pokedex ID, better flush indexes.

Once indexes operations are over, computed type stats will be displayed.

```
 ---------- ---- ----- ------- ------- ----- ----- ---- 
  STAT       #    SPD   S-DEF   S-ATT   DEF   ATT   HP  
 ---------- ---- ----- ------- ------- ----- ----- ---- 
  poison     33   62    64      69      59    68    61  
  grass      14   49    65      83      69    72    63  
  fire       12   84    77      85      63    84    64  
  flying     19   85    70      69      66    84    68  
  water      32   66    66      67      81    71    63  
  bug        12   57    55      48      57    64    55  
  normal     22   69    60      51      54    68    79  
  electric   9    99    73      91      65    62    54  
  ground     14   57    54      45      97    85    60  
  fairy      5    50    70      77      50    55    92  
  fighting   8    67    76      48      65    102   67  
  psychic    14   78    82      98      66    63    70  
  rock       11   54    52      56      110   87    61  
  steel      2    58    63      108     83    48    38  
  ice        5    76    91      90      95    77    85  
  ghost      3    95    55      115     45    50    45  
  dragon     3    67    73      73      68    94    64  
 ---------- ---- ----- ------- ------- ----- ----- ---- 
```

## Licence

This bundle in under the [GPL v3](Resources/meta/LICENCE) licence.
