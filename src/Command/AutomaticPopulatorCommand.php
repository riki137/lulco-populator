<?php

namespace Populator\Command;

use Faker\Factory;
use Populator\Database\DatabaseInterface;
use Populator\Helper\TableAnalyzer;
use Populator\Populator\AutomaticPopulator;
use Populator\Populator\PopulatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutomaticPopulatorCommand extends SimplePopulatorCommand
{
    protected $databaseName;

    protected $columnNameAndTypeClasses;

    protected $countBase;

    private $maxCountPerTable;

    public function __construct(
        DatabaseInterface $database,
        array $columnNameAndTypeClasses = [],
        string $language = Factory::DEFAULT_LOCALE,
        int $countBase = 5,
        int $maxCountPerTable = 125
    ) {
        parent::__construct($database, $language);
        $this->databaseName = $database->getName();
        $this->columnNameAndTypeClasses = $columnNameAndTypeClasses;
        $this->countBase = $countBase;
        $this->maxCountPerTable = $maxCountPerTable;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->populators = $this->createPopulators($this->databases[$this->databaseName]);
        return parent::execute($input, $output);
    }

    /**
     * @param DatabaseInterface $database
     * @return PopulatorInterface[]
     */
    protected function createPopulators(DatabaseInterface $database): array
    {
        $structures = $database->getStructure();
        $tableAnalyzer = new TableAnalyzer();
        $tableDepths = $tableAnalyzer->getDepths($structures);

        $populators = [];
        foreach ($tableDepths as $table => $depth) {
            $count = min($this->maxCountPerTable, pow($this->countBase, $depth + 1));
            $populators[] = new AutomaticPopulator($table, $count, null, 25, 25, $this->columnNameAndTypeClasses);
        }
        return $populators;
    }
}