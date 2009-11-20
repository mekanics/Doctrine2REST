Doctrine 2 REST
===============

This Doctrine 2 extension gives you an automatic REST api for managing the
persistence of your entities.

## Server

To setup a server it is pretty simple. First just create a blank script
named api.php.

First we need to setup our class loader from Doctrine to load our classes:

    // api.php

    require '/path/to/project/lib/Doctrine/Common/IsolatedClassLoader.php';

    $classLoader = new \Doctrine\Common\IsolatedClassLoader();
    $classLoader->setBasePath('/path/to/project/lib');
    $classLoader->register();

You should have the following paths setup in order for the above autoloader to work:

 * /path/to/project/lib/DoctrineExtensions/REST
 * /path/to/project/lib/Doctrine/Common
 * /path/to/project/lib/Doctrine/DBAL
 * /path/to/project/lib/Entities
 * ...

For testing lets create one test model in our Entities folder:

    // lib/Entities/User.php
    
    namespace Entities;

    /**
     * @Entity
     */
    class User
    {
        /**
         * @Id @Column(type="integer")
         * @GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @Column(type="string", length=255, unique=true)
         */
        private $username;

        public function setUsername($username)
        {
            $this->username = $username;
        }
    }

Now we need instantiate our Doctrine EntityManager instance that we will pass
to our REST server:

    $config = new \Doctrine\ORM\Configuration();
    $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
    $config->setProxyDir('/tmp');
    $config->setProxyNamespace('Proxies');

    $connectionOptions = array(
        'driver' => 'pdo_sqlite',
        'path' => 'database.sqlite'
    );

    $em = new \DoctrineExtensions\REST\EntityManager\Wrapper(
        \Doctrine\ORM\EntityManager::create($connectionOptions, $config)
    );

Lets setup our REST server:

    $server = new \DoctrineExtensions\REST\Server($em, $_REQUEST);
    $server->run();

We can now do something like this from the command line to insert a new entity:

    curl --data "username=jonwage" http://localhost/api.php?_action=insert&_entity=Entities\User

Or we can get an entity:

    curl http://localhost/api.php?_action=get&_entity=Entities\User&_id=1

The URLs aren't search engine friendly but that can be easily fixed by implementing
the server in something like Symfony where you can using a routing system to have
nice search engine friendly urls.

## Client

We can easily write a client in any language to interact with the REST interface
produces. This library comes with a PHP client which you can use in your project
or use as an example to build a client in another language.

First instantiate a new client instance:

    $client = new \DoctrineExtensions\REST\Client('http://localhost/api.php');

### List Actions

One of the actions available in the instant REST api is a method which will
return a list of the available actions. It returns information about each action
such as required request method, required arguments, etc. 

    $result = $client->actions();

### List Action

Now we can start executing actions. First lets list some entities:

    $result = $client->list('Entities\User', array('username' => 'jwage'));

You can retrieve pages at a time:

    $result = $client->list('Entities\User', array('_page' => 1));

If you want to specify the number to show per page you can do the following:

    $result = $client->list('Entities\User', array('_page' => 1, '_max_per_page' => 5));

Or you can get specific and pass the start and max values:

    $result = $client->list('Entities\User', array('_start' => 0, '_max' => 100));

### Insert Action

You can insert new entities by using the insert() method:

    $result = $client->insert('Entities\User', array('username' => 'jwage'));

### Update Action

To update an entity just use the update() method:

    $result = $client->update('Entities\User', 1, array('username' => 'jonwage'));

### Get Action

You can get an entity by using the get() method:

    $result = $client->get('Entities\User', 1);

You can retrieve multiple entities by id with the get() method as well:

    $result = $client->get('Entities\User', array(1, 2));

### Delete Action

Delete a single entity:

    $result = $client->delete('Entities\User', 1);

Or you can delete multiple entities:

    $result = $client->delete('Entities\User', array(1, 2));

### DQL Action

One convenient feature of the API is the ability to execute arbitrary DQL queries:

    $result = $client->dql('SELECT u FROM Entities\User u WHERE u.username = :username', array('username' => 'jwage'));

You can even issue a DELETE or UPDATE query:

    $result = $client->dql('DELETE FROM Entities\User u WHERE u.id = 1');

## Using in Symfony

To use this feature is very simple. We simply need to setup an application with 
a routing file. Paste the following routes:

    homepage:
      class: sfRequestRoute
      url:   /
      param: { module: api, action: index, sf_format: json }
      requirements:
        _format: get

    api:
      class: sfRequestRoute
      url:   /api.:sf_format
      param: { module: api, action: index, sf_format: json }
      requirements:
        sf_method: get

    api_dql:
      class: sfRequestRoute
      url:   /api/dql/:_query.:sf_format
      param: { module: api, action: index, _action: dql, sf_format: json }
      requirements:
        sf_method: get

    api_entity_insert:
      class: sfRequestRoute
      url:   /api/:_entity.:sf_format
      param: { module: api, action: index, _action: insert, sf_format: json }
      requirements:
        sf_method: post

    api_entity_list:
      class: sfRequestRoute
      url:   /api/:_entity.:sf_format
      param: { module: api, action: index, _action: list, sf_format: json }
      requirements:
        sf_method: get

    api_entity_get:
      class: sfRequestRoute
      url:   /api/:_entity/:_id.:sf_format
      param: { module: api, action: index, _action: get, sf_format: json }
      requirements:
        _id: \d+
        sf_method: get

    api_entity_update:
      class: sfRequestRoute
      url:   /api/:_entity/:_id.:sf_format
      param: { module: api, action: index, _action: update, sf_format: json }
      requirements:
        _id: \d+
        sf_method: put

    api_entity_delete:
      class: sfRequestRoute
      url:   /api/:_entity/:_id.:sf_format
      param: { module: api, action: index, _action: delete, sf_format: json }
      requirements:
        _id: \d+
        sf_method: delete

Now we just need a module named api which implements the Doctrine 2 REST interface:

    class apiActions extends sfActions
    {
      public function executeIndex(sfWebRequest $request)
      {
          $config = new \Doctrine\ORM\Configuration();
          $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
          $config->setProxyDir('/tmp');
          $config->setProxyNamespace('Proxies');

          $connectionOptions = array(
            'driver' => 'pdo_sqlite',
            'path' => '/path/to/project/data/database.sqlite'
          );

          $em = new \DoctrineExtensions\REST\EntityManager\Wrapper(
            \Doctrine\ORM\EntityManager::create($connectionOptions, $config)
          );

          $requestData = $request->getParameterHolder()->getAll();
          $restRequest = new \DoctrineExtensions\REST\Request($requestData);
          $restResponse = new \DoctrineExtensions\REST\Response($restRequest);
          $requestHandler = new \DoctrineExtensions\REST\RequestHandler($em, $restRequest, $restResponse);

          $restRequest['_method'] = strtolower($request->getMethod());
          $restRequest['_format'] = $request->getParameter('sf_format');

          unset(
            $restRequest['module'],
            $restRequest['action'],
            $restRequest['sf_format']
          );

          $requestHandler->getResponse()->send();
          exit;
      }
    }

The result of this is that the URLs are much more friendly. For example to get 
an entity with curl would look like the following with a simple get request:

    curl http://localhost/api/Entities\User/1.xml

Or if you want to update that entity we need to issue a put request:

    curl --data "sf_method=put&username=newusername" http://localhost/api/Entities\User/1.xml

Or to delete that user we need to issue a delete request:

    curl --data "sf_method=delete" http://localhost/api/Entities\User/1.xml

We can insert a new user just as easily by issuing a post request:

    curl --data "username=jwage" http://localhost/api/Entities\User.xml