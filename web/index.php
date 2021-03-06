<?php

ini_set("memory_limit", "256M");    // doing this is annoying but heh I've not got a better method *yet*
 
require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('UTC');   // compulsory thing to stop errors
 
$app = new Silex\Application();
 
$app["debug"] = true;   // you'll want to turn this to false in production
 
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get("/", function () use ($app) {
    // declare php-github clients
    $client = new \Github\Client($client);
    $paginator  = new Github\ResultPager($client);

    // get .env file and load the token from it
    Dotenv::load(__DIR__.'/../');
    $token = getenv('GITHUB_TOKEN');

    // authenticate with github
    $client->authenticate($token, Github\Client::AUTH_HTTP_TOKEN);

    // get the user and the user repositories
    $user = $client->api('organization')->show('purplapp');
    $repositories = $client->api('repo')->show('purplapp', 'purplapp');

    $repo_contributors = $client->api('repo')->contributors('purplapp', 'purplapp', false);

    // get the languages used in the repository
    $repo_language = $client->api('repo')->languages('purplapp', 'purplapp');
    // $repo_language = new LanguageCollection($repo_language);

    // get the pull requests for the repository
    $repo_pull = $client->api('pull_request')->all('purplapp', 'purplapp', array('state' => 'all'));
    $repo_pull_comments_response = $client->getHttpClient()->get('/repos/purplapp/purplapp/comments');
    $repo_pull_comments = Github\HttpClient\Message\ResponseMediator::getContent($repo_pull_comments_response);

    // get the releases from the repository
    $repo_releases = $client->api('repo')->releases()->all('purplapp', 'purplapp');

    $repo_statistics = $client->api('repo')->statistics('purplapp', 'purplapp');

    // get total number of commits
    $commitsApi = $client->repo()->commits();
    $parameters = array('purplapp', 'purplapp', array('sha' => 'master'));
    $repo_commits = $paginator->fetchAll($commitsApi, 'all', $parameters);

    // get total number of issues
    $issuesApi = $client->issues();
    $parameters = array('purplapp', 'purplapp', array('state' => 'all'));
    $repo_issues = $paginator->fetchAll($issuesApi, 'all', $parameters);

    // get total number of comments on issues
    $issuesCommentsApi = $client->issues()->comments();
    $parameters = array('purplapp', 'purplapp', '');
    $repo_issues_comments = $paginator->fetchAll($issuesCommentsApi, 'all', $parameters);

    // render the twig file
    return $app["twig"]->render(
        "index.html.twig",
        compact("user", "repositories", "repo_contributors", "repo_language", "repo_issues", "repo_pull", "repo_commits", "repo_pull_comments", "repo_issues_comments", "repo_releases", "repo_issues_events", "repo_statistics")
    );
});

// declare the routes
$routes = array(
    'home' => array('url' => '/', 'template' => 'index.html.twig')
);

// routes related stuff
foreach ($routes as $routeName => $data) {
    $app->get($data['url'], function() use($app, $data) {
        return $app['twig']->render($data['template']);
    })->bind($routeName);
}

// run the app
$app->run();