<?php

namespace Terraformers\KeysForCache;

use App\Elemental\Blocks\HeroImageBlock;
use DNADesign\Elemental\Models\BaseElement;
use Page;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use Terraformers\KeysForCache\RelationshipGraph\Edge;

class CacheTestTask extends BuildTask
{
    /**
     * @param HTTPRequest|mixed $request
     */
    public function run($request): void
    {
//        $b = HeroImageBlock::get()->find('ID', 111);
        $b = Image::get()->find('ID', 2);
        $b->Title = time();
        $b->write();
//        $classThatWasEdited = HeroImageBlock::class;
//        $this->simulateClassUpdate($classThatWasEdited);
    }

    public function simulateClassUpdate(string $className): void
    {
        $config = CacheRelationService::singleton()->getGraph();
        $classesToUpdate = [$className];
        $edgesUpdated = [];


        while (count($classesToUpdate) > 0) {
            $current = array_pop($classesToUpdate);

            if (!$current) {
                continue;
            }

            self::log(sprintf("Updating %s", $current));
            $edges = $config->getEdges($current);
            $edgesUpdated[] = $current;

            /** @var Edge $edge */
            foreach ($edges as $edge) {
                $to = $edge->getToClassName();

                if (in_array($to, $edgesUpdated)) {
                    continue;
                }

                self::log(sprintf(
                    '%s updates %s through "%s"',
                    $current,
                    $to,
                    $edge->getRelation()
                ));
                $classesToUpdate[] = $to;
            }
        }
    }

    public static function log(string $message): void
    {
        $break = Director::is_cli()
            ? PHP_EOL
            : '<br>';

        echo sprintf('%s%s', $message, $break);
    }
}
