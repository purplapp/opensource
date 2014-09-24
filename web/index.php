<?php
 
// set_time_limit(0);

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('UTC');
 
$app = new Silex\Application();
 
$app["debug"] = true;
 
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get("/", function () use ($app) {
    $client = new \Github\Client($client);

    Dotenv::load(__DIR__.'/../');

    $token = getenv('GITHUB_TOKEN');

    $client->authenticate($token, Github\Client::AUTH_HTTP_TOKEN);

	$repositories = $client->api('repo')->show('purplapp', 'purplapp');
    $repo_contributors = $client->api('repo')->contributors('purplapp', 'purplapp', false);
    $repo_language = $client->api('repo')->languages('purplapp', 'purplapp');
    // $repo_language = new LanguageCollection($repo_language);
    $repo_issues = $client->api('issue')->all('purplapp', 'purplapp', array('state' => 'all'));

    return $app["twig"]->render(
        "index.html.twig",
        compact("repositories", "repo_contributors", "repo_language", "repo_issues")
    );
});

$routes = array(
    'home' => array('url' => '/', 'template' => 'index.html.twig')
);

foreach ($routes as $routeName => $data) {
    $app->get($data['url'], function() use($app, $data) {
        return $app['twig']->render($data['template']);
    })->bind($routeName);
}

$app->run();