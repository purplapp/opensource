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

    $paginator  = new Github\ResultPager($client);

    Dotenv::load(__DIR__.'/../');

    $token = getenv('GITHUB_TOKEN');

    $client->authenticate($token, Github\Client::AUTH_HTTP_TOKEN);

    $user = $client->api('organization')->show('purplapp');
	$repositories = $client->api('repo')->show('purplapp', 'purplapp');

    // $repo_contributors = $client->api('repo')->contributors('purplapp', 'purplapp', false);

    $repo_language = $client->api('repo')->languages('purplapp', 'purplapp');
    // $repo_language = new LanguageCollection($repo_language);

    $repo_pull = $client->api('pull_request')->all('purplapp', 'purplapp', array('state' => 'all'));
    $repo_pull_comments_response = $client->getHttpClient()->get('/repos/purplapp/purplapp/comments');
    $repo_pull_comments = Github\HttpClient\Message\ResponseMediator::getContent($repo_pull_comments_response);

    $repo_releases = $client->api('repo')->releases()->all('purplapp', 'purplapp');

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

    // get total number of contributors
    $contributorsApi = $client->repo();
    $parameters = array('purplapp', 'purplapp', 'false');
    $repo_issues_comments = $paginator->fetchAll($contributorsApi, 'contributors', $parameters);

    return $app["twig"]->render(
        "index.html.twig",
        compact("user", "repositories", "repo_contributors", "repo_language", "repo_issues", "repo_pull", "repo_commits", "repo_pull_comments", "repo_issues_comments", "repo_releases", "repo_issues_events")
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