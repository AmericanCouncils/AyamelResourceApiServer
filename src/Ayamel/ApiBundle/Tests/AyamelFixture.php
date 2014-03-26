<?php

namespace Ayamel\ApiBundle\Tests;

use AC\WebServicesBundle\Fixture\CachedMongoFixture;

class AyamelFixture extends CachedMongoFixture
{
    // should we perhaps validate this fixture against the model constraints?
    protected function fixture()
    {
        $this->describe('AyamelResourceBundle:OEmbed',[
            'type' => function($f) {return $f->fake()->word();},
            'version' => function($f) {return "1.0";},
            'title' => function($f) {return $f->fake()->sentence(5);},
            'author_name' => function($f) {return $f->fake()->name();},
            'author_url' => function($f) {return $f->fake()->url();},
            'provider_name' => function($f) {return $f->fake()->sentence(3);},
            'provider_url' => function($f) {return $f->fake()->url();},
            'thumbnail_url' => function($f) {return $f->fake()->url();},
            'thumbnail_height' => function($f) {return $f->fake()->randomDigit(3);},
            'thumbnail_width' => function($f) {return $f->fake()->randomDigit(3);},
            'cache_age' => function($f) {return $f->fake()->randomDigit(5);},
            // 'html' => function($f) {return $f->fake()->something();},
            'url' => function($f) {return $f->fake()->url();},
            'height' => function($f) {return $f->fake()->randomDigit(6);},
            'width' => function($f) {return $f->fake()->randomDigit(6);}
        ]);
        $this->describe("AyamelResourceBundle:FileReference", [
            'downloadUri' => function($f) {return $f->fake()->url();},
            'streamUri' => function($f) {return $f->fake()->url();},
            'internalUri' => function($f) {return $f->fake()->url();},
            'bytes' => function($f) {return $f->fake()->randomDigit(10);},
            'representation' => function($f) {return $f->fake()->randomElement(['original', 'transcoding', 'summary']);},
            'quality' => function($f) {return $f->fake()->randomDigit();},
            'mime' => function($f) {return $f->fake()->mimeType();},
            'mimeType' => function($f) {return $f->fake()->mimeType();},
            'attributes' => function($f) {return [];},
            ]);
        $this->describe("AyamelResourceBundle:ContentCollection", [
            'canonicalUri' => function ($f) {return $f->fake()->url();},
            'files' => function ($f) {return $f->build(1, "AyamelResourceBundle:FileReference");},
            'oembed' => function ($f) {return $f->buildOne("AyamelResourceBundle:OEmbed");},
        ]);
        $this->describe("AyamelResourceBundle:Client", [
            'id' => function ($f) {return "another-test-client";},
            'name' => function ($f) {return "Another Test Client";},
            'uri' => function ($f) {return "http://www.anothertestclient.com";},
        ]);
        $this->generate(10, "AyamelResourceBundle:Resource", [
            'title' => function ($f) {return $f->fake()->sentence(3);},
            'description' => function ($f) {return $f->fake()->sentence(20);},
            // 'keywords' => function ($f) {return $this->commaDelimitedString($f, 5);},
            'keywords' => function ($f) {return $f->fake()->word() . ',' . $f->fake()->word() . ',' . $f->fake()->word();},
            'subjectDomains' => function ($f) {return [$f->fake()->randomElement(["Arts", "Entertainment", "Culture", "Economy", "Education", "Food", "Geography", "History", "News", "Politics", "Religion", "Sports", "Technology", "Weather", "Other"])];},
            'functionalDomains' => function ($f) {return [$f->fake()->randomElement(['Foo','Bar','Baz'])];},
            'registers' => function ($f) {return [$f->fake()->randomElement(['formal', 'casual', 'intimate', 'static', 'consultative'])];},
            'type' => function ($f) {return $f->fake()->randomElement(['video', 'audio', 'image', 'document', 'collection']);},
            'sequence' => function ($f) {return $f->fake()->boolean();}, //really this should be conditional on type
            'visibility' => function ($f) {return [];}, //empty array, visible to everyone
            'dateAdded' => function ($f) {return $f->fake()->dateTimeBetween('-2 years','-1 years');},
            'dateModified' => function ($f) {return $f->fake()->dateTimeBetween('-1 years','now');},
            'copyright' => function ($f) {return $f->fake()->catchPhrase();},
            'license' => function ($f) {return $f->fake()->bs();},
            'status' => function ($f) {return $f->fake()->randomElement(['normal','awaiting_processing','processing']);},
            'content' => function ($f) {return $f->buildOne("AyamelResourceBundle:ContentCollection");},
            'client' => function ($f) {return $f->buildOne("AyamelResourceBundle:Client");}
            // 'clientUser' => function ($f) {return $f->fake()->something();},
            // 'dateDeleted' => function ($f) {return $f->fake()->dateTimeBetween('now','+5 years');},
            // 'relations' => function ($f) {return $f->fake()->something();},
        ]);
        $this->generate(10, "AyamelResourceBundle:Relation", [
            'subjectId' => function ($f) {return $f->fetchCorresponding("AyamelResourceBundle:Resource")->getId();},
            'objectId' => function ($f) {return $f->fetchCorresponding("AyamelResourceBundle:Resource")->getId();},
            'type' => function ($f) {return $f->fake()->randomElement(['based_on', 'references', 'requires', 'transcript_of', 'search', 'version_of', 'part_of', 'translation_of', 'contains']);},
            'attributes' => function ($f) {return [];},  // valid values conditional on type - could do this properly, for now just leave as empty array
        ]);
    }
}
