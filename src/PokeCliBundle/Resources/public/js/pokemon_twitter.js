// Twitter JS Widget init
// if(typeof twttr == "undefined") {
    window.twttr = (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
        if (d.getElementById(id)) return t;
        js = d.createElement(s);
        js.id = id;
        js.src = "https://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js, fjs);

        t._e = [];
        t.ready = function (f) {
            t._e.push(f);
        };

        return t;
    }(document, "script", "twitter-wjs"));
// }


var pokeContainer = ".pokemonTimeline";

$(document).ready(function () {
    // Twitter Timeline
    if ($(pokeContainer).length) {
        var pokemonName = $(pokeContainer).attr('id');

        // get latests tweets
        $.ajax('/api/pokemon/tweets/' + pokemonName).done(function(tweets){
            $.each(tweets.tweets, addTweet);
        });
    }
});

function addTweet(i, tweetID)
{
    $(pokeContainer).append('<div id="' + tweetID + '"></div>');
    twttr.widgets.createTweet(
        tweetID.toString(),
        document.getElementById(tweetID));
}



