<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function sprintf;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Migrator
{
    /**
     * @throws Exception
     * @throws MigrationBuilderException
     * @throws MigrationException
     * @throws XmlException
     */
    public function migrate(string $filename): string
    {
        $origin = (new SchemaDetector)->detect($filename);

        if (!$origin->detected()) {
            throw new Exception(
                sprintf(
                    '%s does not validate against any know schema',
                    $filename,
                ),
            );
        }

        $configurationDocument = (new XmlLoader)->loadFile($filename);

        foreach ((new MigrationBuilder)->build($origin->version()) as $migration) {
            $migration->migrate($configurationDocument);
        }

        $configurationDocument->formatOutput       = true;
        $configurationDocument->preserveWhiteSpace = false;

        return $configurationDocument->saveXML();
    }
}
