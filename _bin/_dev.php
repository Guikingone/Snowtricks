<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$datetime = new DateTime();
$path = $datetime->format('d-m-Y').'_'.($datetime->format('H') + 1).'h'.$datetime->format('i').'min';

exec('php bin/console d:d:d --force');
exec('php bin/console d:d:c');
exec('php bin/console d:s:u --force');
exec('php-cs-fixer fix ./src --level=symfony');
exec('php-cs-fixer fix ./tests --level=symfony');
exec('php bin/console d:f:l -n');
exec('phpunit --coverage-html ./_coverage/'.$path);
exec('phpmetrics --report-html ./_quality/_metrics.html ./src');
exec('phploc ./src');
