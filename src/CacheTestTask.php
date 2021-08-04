<?php

namespace Terraformers\KeysForCache;

use App\Elemental\Blocks\HeroImageBlock;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;

class CacheTestTask extends BuildTask
{
    /**
     * @param HTTPRequest|mixed $request
     */
    public function run($request): void
    {
        $config = CacheRelationService::singleton()->getRelationConfig();
//        $result = $config->getEdges(HeroImageBlock::class);
//        echo '<pre>'; print_r($result); exit;

        $classThatWasEdited = HeroImageBlock::class;
        $this->simulateClassUpdate($classThatWasEdited);


//        $result = $config;
//        $result = $config->getEdges(Image::class);
//
//        echo '<pre>';
//        print_r($result);
//        echo '</pre>';
    }

    public function simulateClassUpdate(string $className): void
    {
        $config = CacheRelationService::singleton()->getRelationConfig();
        $classesToUpdate = [$className];
        $edgesUpdated = [];

        $passes = 0;

        while (count($classesToUpdate) > 0 && $passes <= 50) {
            $passes += 1;
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
