<?php /** @noinspection PhpIncludeInspection */

/** @var EntityManager $em */
require 'bootstrap.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet($c->em);

