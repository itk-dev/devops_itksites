<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\DetectionResult;
use PHPUnit\Framework\TestCase;

class DetectionResultTest extends TestCase
{
    public function testPrettyDataIndentsJsonWithoutEscapingSlashesOrUnicode(): void
    {
        $result = new DetectionResult()->setData('{"remotes":["https:\/\/github.com\/itk-dev\/sites"],"name":"Ærø"}');

        $this->assertSame(<<<'JSON'
            {
                "remotes": [
                    "https://github.com/itk-dev/sites"
                ],
                "name": "Ærø"
            }
            JSON, $result->getPrettyData());
    }

    public function testPrettyDataReturnsDataThatIsNotJsonAsIs(): void
    {
        $result = new DetectionResult()->setData('not json');

        $this->assertSame('not json', $result->getPrettyData());
    }
}
