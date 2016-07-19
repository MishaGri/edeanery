<?php
error_reporting(E_ALL);
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Debug;
use Phalcon\Session\Adapter\Files as Session;

try {

    // Регистрируем автозагрузчик
    $loader = new Loader();
    $loader->registerDirs(array(
        '../app/controllers/',
        '../app/models/'
    ))->register();
    (new Debug)->listen();
    // Создаем DI
    $di = new FactoryDefault();

    // Настраиваем доступ к БД
    $di->set('db', function () {
        return new DbAdapter(array(
            "host"     => "localhost",
            "username" => "root2",
            "password" => "grizli",
            "dbname"   => "attend",
            "charset"  => 'UTF8'
        ));
    });
    // Настраиваем компонент View
    $di->set('view', function () {
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    });

    // Настраиваем базовый URI так, чтобы все генерируемые URI содержали директорию "tutorial"
    $di->set('url', function () {
        $url = new UrlProvider();
        $url->setBaseUri('/');
        return $url;
    });
    $di->setShared('transactions', function () {
        return new TransactionManager();
    });

    // Обрабатываем запрос
    $application = new Application($di);
    $di->setShared('session', function () {
        $session = new Session();
        $session->start();
        return $session;
    });
    echo $application->handle()->getContent();
} catch (\Exception $e) {
     echo "Exception: ", $e->getMessage();
}