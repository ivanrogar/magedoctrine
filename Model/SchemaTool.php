<?php

declare(strict_types=0);

namespace JohnRogar\MageDoctrine\Model;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Modified schema tool class which doesn't tamper with tables other than Doctrine's
 *
 * Class SchemaTool
 * @package JohnRogar\MageDoctrine\Model
 * @SuppressWarnings(ShortVariable)
 */
class SchemaTool extends \Doctrine\ORM\Tools\SchemaTool
{

    protected const KNOWN_COLUMN_OPTIONS = ['comment', 'unsigned', 'fixed', 'default'];

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    protected $platform;

    /**
     * The quote strategy.
     *
     * @var \Doctrine\ORM\Mapping\QuoteStrategy
     */
    protected $quoteStrategy;

    /**
     * SchemaTool constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em               = $em;
        $this->platform         = $em->getConnection()->getDatabasePlatform();
        $this->quoteStrategy    = $em->getConfiguration()->getQuoteStrategy();
        parent::__construct($em);
    }

    /**
     * @param array $classes
     * @param bool $saveMode
     * @return array|string[]
     * @SuppressWarnings(BooleanArgumentFlag)
     */
    public function getUpdateSchemaSql(array $classes, $saveMode = false)
    {
        $sm = $this->em->getConnection()->getSchemaManager();

        $tables = [];
        foreach ($classes as $class) {
            if (isset($class->table) && isset($class->table["name"])) {
                $tables[] = $sm->listTableDetails($class->table["name"]);
            }
        }

        $sequences = ($sm->getDatabasePlatform()->supportsSequences() ? $sm->listSequences() : [] );
        $fromSchema = new Schema($tables, $sequences, $sm->createSchemaConfig());

        $toSchema = $this->getSchemaFromMetadata($classes);

        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        if ($saveMode) {
            return $schemaDiff->toSaveSql($this->platform);
        }

        return $schemaDiff->toSql($this->platform);
    }
}
