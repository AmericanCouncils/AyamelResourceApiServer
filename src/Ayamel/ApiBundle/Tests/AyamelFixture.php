<?php

namespace Ayamel\ApiBundle\Tests;

use AC\WebServicesBundle\Fixture\CachedMongoFixture;

class AyamelFixture extends CachedMongoFixture
{
    // should we perhaps validate this fixture against the model constraints?
    protected function fixture()
    {
        $this->describe('AyamelResourceBundle:OEmbed',[
            'type' => function ($f) {return $f->fake()->word();},
            'version' => function ($f) {return "1.0";},
            'title' => function ($f) {return $f->fake()->sentence(5);},
            'author_name' => function ($f) {return $f->fake()->name();},
            'author_url' => function ($f) {return $f->fake()->url();},
            'provider_name' => function ($f) {return $f->fake()->sentence(3);},
            'provider_url' => function ($f) {return $f->fake()->url();},
            'thumbnail_url' => function ($f) {return $f->fake()->url();},
            'thumbnail_height' => function ($f) {return $f->fake()->randomDigit(3);},
            'thumbnail_width' => function ($f) {return $f->fake()->randomDigit(3);},
            'cache_age' => function ($f) {return $f->fake()->randomDigit(5);},
            // 'html' => function ($f) {return $f->fake()->something();},
            'url' => function ($f) {return $f->fake()->url();},
            'height' => function ($f) {return $f->fake()->randomDigit(6);},
            'width' => function ($f) {return $f->fake()->randomDigit(6);}
        ]);

        $this->describe("AyamelResourceBundle:FileReference", [
            'downloadUri' => function ($f) {return $f->fake()->url();},
            'streamUri' => function ($f) {return $f->fake()->url();},
            'internalUri' => function ($f) {return $f->fake()->url();},
            'bytes' => function ($f) {return $f->fake()->randomDigit(10);},
            'representation' => function ($f) {return $f->fake()->randomElement(['original', 'transcoding', 'summary']);},
            'quality' => function ($f) {return $f->fake()->randomDigit();},
            'mime' => function ($f) {return $f->fake()->mimeType();},
            'mimeType' => function ($f) {return $f->fake()->mimeType();},
            'attributes' => function ($f) {return [];},
        ]);

        $this->describe("AyamelResourceBundle:Languages", [
            'iso639_3' => function ($f) {return array_unique($f->fake()->randomElements(['eng','rus','arq'], $f->fake()->randomNumber(0,3)));},
            'bcp47' => function ($f) {return array_unique($f->fake()->randomElements(['en','ru','ar','fr'], $f->fake()->randomNumber(0,4)));},
        ]);

        $this->describe("AyamelResourceBundle:ContentCollection", [
            'canonicalUri' => function ($f) {return $f->fake()->url();},
            'files' => function ($f) {return $f->build(1, "AyamelResourceBundle:FileReference");},
            'oembed' => function ($f) {return $f->buildOne("AyamelResourceBundle:OEmbed");},
        ]);

        $this->describe("AyamelResourceBundle:Client", [
            'id' => function ($f) {return $f->fake()->randomElement(["test-client", "another-test-client"]);},
            'name' => function ($f) {return $f->fake()->randomElement(["Test Client 1","Test Client 2"]);},
            'uri' => function ($f) {return "http://www.anothertestclient.com";},
        ]);
        $clients = $this->build(5, 'AyamelResourceBundle:Client');

        $this->describe("AyamelResourceBundle:ClientUser", [
            'id' => function ($f) { return 'user-'.$f->fake()->randomElement(range(1,10)); },
            'url' => function ($f) { return 'http://example.com/users/'.$f->curObject()->getId(); }
        ]);
        $clientUsers = $this->build(10, 'AyamelResourceBundle:ClientUser');

        $this->generate(50, "AyamelResourceBundle:Resource", [
            'title' => function ($f) {return $f->fake()->sentence(3);},
            'description' => function ($f) {return $f->fake()->sentence(20);},
            'keywords' => function ($f) {return $f->fake()->word() . ',' . $f->fake()->word() . ',' . $f->fake()->word();},
            'subjectDomains' => function ($f) {return array_unique($f->fake()->randomElements(["arts", "science", "literature", "entertainment", "culture", "economy", "education", "food", "geography", "history", "news", "politics", "religion", "sports", "technology", "weather", "other"], $f->fake()->randomNumber(1,7)));},
            'functionalDomains' => function ($f) {return array_unique($f->fake()->randomElements(['informative','presentational','interactive'], $f->fake()->randomNumber(1,2)));},
            'registers' => function ($f) {return array_unique($f->fake()->randomElements(['formal', 'casual', 'intimate', 'static', 'consultative'], $f->fake()->randomNumber(1,3)));},
            'type' => function ($f) {return $f->fake()->randomElement(['video', 'audio', 'image', 'document', 'collection']);},
            'sequence' => function ($f) {return $f->fake()->boolean();}, //really this should be conditional on type
            'visibility' => function ($f) {return $f->fake()->randomElement([[], ['test_client2'], ['test_client2','test_client']]); },
            'dateAdded' => function ($f) {return $f->fake()->dateTimeBetween('-2 years','-1 years');},
            'dateModified' => function ($f) {return $f->fake()->dateTimeBetween('-1 years','now');},
            'copyright' => function ($f) {return $f->fake()->catchPhrase();},
            'license' => function ($f) {return $f->fake()->randomElement(['CC BY', 'CC BY-SA', 'CC BY-NC', 'CC BY-ND', 'CC BY-NC-SA', 'CC BY-NC-ND']);},
            'status' => function ($f) {return $f->fake()->randomElement(['normal','awaiting_processing','processing']);},
            'content' => function ($f) {return $f->buildOne("AyamelResourceBundle:ContentCollection");},
            'languages' => function ($f) {return $f->buildOne('AyamelResourceBundle:Languages');},
            'client' => function ($f) use ($clients) {return $f->fake()->randomElement($clients);},
            'clientUser' => function ($f) use ($clientUsers) {return $f->fake()->randomElement($clientUsers);},
            // 'dateDeleted' => function ($f) {return $f->fake()->dateTimeBetween('now','+5 years');},
        ]);
        $this->generate(50, "AyamelResourceBundle:Relation", [
            'subjectId' => function ($f) {return $f->fetchCorresponding("AyamelResourceBundle:Resource")->getId();},
            'objectId' => function ($f) {return $f->fetchCorresponding("AyamelResourceBundle:Resource")->getId();},
            'type' => function ($f) {return $f->fake()->randomElement(['based_on', 'references', 'requires', 'transcript_of', 'search', 'version_of', 'part_of', 'translation_of', 'contains']);},
            'attributes' => function ($f) {return [];},  // valid values conditional on type - could do this properly, for now just leave as empty array
        ]);
    }
}
