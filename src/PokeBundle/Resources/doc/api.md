# Pokepedia API

## GET methods

#### /api/pokemon/popularity/{name}

Returns the popularity of the Pokemon (ie. likes and dislikes)

`GET /api/pokemon/popularity/poliwhirl`
```
{
    "pokemon": "poliwhirl",
    "popularity":
    {
        "likes": "0",
        "dislikes": "0"
    }
}
```

#### /api/pokemon/random
Returs a random Pokemon 

`GET /api/pokemon/random`
```
{
    "baseExperience": 155,
    "weight": 38,
    "height": 1.2,
    "name": "fearow",
    "iD": 22,
    "types":
    [
        "flying",
        "normal"
    ],
    "stats":
    {
        "speed": 100,
        "special-defense": 61,
        "special-attack": 61,
        "defense": 65,
        "attack": 90,
        "hp": 65
    },
    "sprites":
    {
        "default":
        {
            "front": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/22.png",
            "back": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/back/22.png"
        }
    }
}
```

#### /api/pokemon/details/{name}

Returns data for the the given Pokemon name.

`GET /api/pokemon/details/seel`
```
{
    "baseExperience": 65,
    "weight": 90,
    "height": 1.1,
    "name": "seel",
    "iD": 86,
    "types":
    [
        "water"
    ],
    "stats":
    {
        "speed": 45,
        "special-defense": 70,
        "special-attack": 45,
        "defense": 55,
        "attack": 45,
        "hp": 65
    },
    "sprites":
    {
        "default":
        {
            "front": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/86.png",
            "back": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/back/86.png"
        }
    }
}
```

#### /api/types

Returns average stats for each Pokemon Type.

`GET /api/types`
```
{
    "water":
    {
        "total": "32",
        "speed": "66",
        "special-defense": "66",
        "special-attack": "67",
        "defense": "81",
        "attack": "71",
        "hp": "63"
    },
    "steel":
    {
        "total": "2",
        "speed": "58",
        "special-defense": "63",
        "special-attack": "108",
        "defense": "83",
        "attack": "48",
        "hp": "38"
    },
    [...]
    "dragon":
    {
        "total": "3",
        "speed": "67",
        "special-defense": "73",
        "special-attack": "73",
        "defense": "68",
        "attack": "94",
        "hp": "64"
    }
}
```

#### /api/search/{pattern}

Returns search results for the given pattern. 

Note: "*" search will returns all results.

`GET /api/search/v`
```
{
    "pattern": "v",
    "count": 8,
    "results":
    [
        "vulpix",
        "venomoth",
        "vileplume",
        "venonat",
        "vaporeon",
        "victreebel",
        "voltorb",
        "venusaur"
    ]
}
```

#### /api/pokemon/tweets/{name}
Returns the latests tweet IDs related the the given Pokemon name.


`GET /api/pokemon/tweets/blastoise`
```
{
    "pokemon": "blastoise",
    "tweets":
    [
        "826307975425843200",
        "826148711348727808",
        "825891800338558976",
        "825879624215359490",
        "825874208852946944",
        "825717727918702592",
        "825664743637655552",
        "825526766454370306",
        "825421462974636032",
        "825402804193746944",
        "825365458446487552"
    ]
}
```

## PUT methods

#### /api/pokemon/like/{name}

Increments the Likes counter for the given Pokemon

`PUT /api/pokemon/like/ditto`
```
{
    "name": "ditto",
    "likes": 3
}
```


#### /api/pokemon/dislike/{name}

Increments the Dislikes counter for the given Pokemon

`PUT /api/pokemon/dislike/golem`
```
{
    "name": "golem",
    "dislikes": 6
}
```