<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add published_at in `entry` table
 */
class Version20161118160546 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'postgresql':
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' ADD published_at TIMESTAMP');
                break;
            default:
                $this->addSql('ALTER TABLE '.$this->getTable('entry').' ADD published_at DATETIME NULL');
                break;
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'sqlite', 'This down migration can\'t be executed on SQLite databases, because SQLite don\'t support DROP COLUMN.');

        $this->addSql('ALTER TABLE '.$this->getTable('entry').' DROP published_at');
    }
}
