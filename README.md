# Twitter API

[![Build Status](https://travis-ci.org/webeism/Tweetings.svg?branch=master)](https://travis-ci.org/webeism/Tweetings)

Experimenting with the twitter REST API (https://dev.twitter.com/rest/public) using:

- Jade: Template (template.jade) which creates the template.html base file
- Sass: sass/css.scss which creates the css/css.css file
- PHP: A php class (clsTweetings.php) using twitter API application-only authorisation
- HTML: Small web interface to add settings and query twitter. Simple "template" system

A working version of the app deployed on Heroku can be <a target="_blank" href="https://webeism-tweetings.herokuapp.com/index.php">see here</a>

TODO: Add methods / options to interface, do something with the tweets, get a life :)

## Installation

Update the inc/config.php file with your twitter API consumer key and secret

## Usage

Visit index.php in your browser, add a twitter user to investigate, press investigate!

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D