<?php

namespace Ayamel\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renames a field in mongodb to a new name.  Will allow specifying nested fields via dot syntax.
 *
 * @author Evan Villemez
 */
class RenameFieldCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:field:rename')
            ->setDescription('Rename a field in the resource structure to a new name.')
            ->setDefinition(array(
                new InputArgument('collection', InputArgument::REQUIRED, 'Name of the mongodb collection to update.'),
                new InputArgument('oldFieldName', InputArgument::REQUIRED, 'The name of the old field to rename.  If nested, specify with dot syntax.'),
                new InputArgument('newFieldName', InputArgument::REQUIRED, "The name of the new field.  If nested, also specify with dot syntax."),
                new InputOption('update', null, InputOption::VALUE_NONE, 'Specifying this flag will actually update data in the database.  Otherwise, the command will just return counts for records to be modified.'),

            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $input->getArgument('collection');
        $oldFieldName = $input->getArgument('oldFieldName');
        $newFieldName = $input->getArgument('newFieldName');

        //get the collection in question
        $mongo = $this->getContainer()->get('doctrine_mongodb.odm.default_connection');
        $db = $mongo->ayamel;
        $col = $db->$collection;

        //find relevant documents
        $results = $col->find(array($oldFieldName => array('$exists' => true)), array($oldFieldName => true));
        $output->writeln(sprintf("Found <info>%s</info> documents containing field <info>%s</info>.", $results->count(), $oldFieldName));

        /*
        $mongoCode = new \MongoCode(sprintf('

            return db.ayamel.%s.find({%s: {$exists: 1}}, {%s: 1}).count();
        ', $collection, $oldFieldName, $oldFieldName));
        */

        $mongoCode = new \MongoCode('return db.ayamel.resources.count();');

    /*
        //if set to update, do it...
        if ($input->getOption('update')) {

            //defines js to be executed by mongo to rename fields
            $mongoCode = new \MongoCode(sprintf("
                var updated = 0;
                function renameField(doc)
                {
                    if (doc.content.files) {
                        foreach (file in doc.content.files) {
                            if (file.publicUri) {
                                file.downloadUri = file.publicUri;
                                file.publicUri = null;
                            }
                        }

                        db.resources.save(doc);
                        updated++;
                    }
                    dNo = doc.technicalData["%s"];
                    db.%s.update({"_id":doc._id}, {
                       $set: {"%s" : dNo},
                       $unset: {"%s":1}
                    });
                }

                db.ayamel.find({"%s":{$ne:null}}).forEach(renameField);

                return updated;
                ",
            ));

            //TODO: rename fields
            //$col->command($mongoCode); ??
        }
        */

        $output->writeln($db->execute($mongoCode));

        return;
    }
}
