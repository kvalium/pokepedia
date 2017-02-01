# PokeCliBundle

This bundle is a front-end implementation of the [PokeRestBundle](/src/PokeRestBundle/README.md).

## Technical stack
* Symfony 3

## Dependencies
* [Chart.js](http://www.chartjs.org/)
* bower

## Index page
### url: /

Index page presents a search form. Entered value will be used as pattern for the PokeRestBundle search API call. 

Empty and  “*” searches are allowed and will returns full index.

Below the search input, a link is present and will returns a random Pokemon detail page using the PokeRestBundle 
random API call.

![Index Page](http://i.imgur.com/OqdNjb1.png)

## Search Results
### url: /search/{pattern|null}

This page lists search results for the given pattern, or all results when no pattern was given. 
Each link is a Pokedex entry and will redirects to the Pokemon Details page using the PokeRestBundle details API call.

![Search Results](http://i.imgur.com/g6tkPf3.png)

## Pokemon Details Page
### url: /details/{pokemon}

Present some data about the Pokemon. Chart present the base stats of the Pokemon (in dark blue) and the average base 
stats of the Pokemon sharing the same type (eg. Grass and Bug for Parasect). 

By default, other types data are disabled but user can click on them to add these data to the chart. In the other hand, 
clicking on a displayed data will hide it. 

Finally, the lists of the latests tweets having the hashtag #{pokemon} (eg. #Parasect) is displayed. 

![Details Page](http://i.imgur.com/kf0c9Dm.png)

## TODO

- [ ] Implements likes / dislikes on details pages
- [ ] Transform Timeline and Chart scripts in JQuery plugins
- [ ] Finalize messages translations

## Author
Julien Monchany - julien.monchany@gmail.com

## Licence

This bundle in under the [GPL v3](Resources/meta/LICENCE) licence.
