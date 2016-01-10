<?php
/**
 * SweetAnnotations - Will load all Annotation files into current run.
 *
 * @author     Tom Valk <tomvalk@lt-box.info>
 * @copyright  2016 Tom Valk
 */


require_once __DIR__ . '/Annotation/Entity.php';
require_once __DIR__ . '/Annotation/Column.php';
require_once __DIR__ . '/Annotation/Table.php';

require_once __DIR__ . '/Annotation/OneToOne.php';
require_once __DIR__ . '/Annotation/OneToMany.php';
require_once __DIR__ . '/Annotation/ManyToOne.php';
require_once __DIR__ . '/Annotation/Join.php';
