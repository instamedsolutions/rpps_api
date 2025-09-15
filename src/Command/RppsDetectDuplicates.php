<?php

namespace App\Command;

use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

// Do not review. @bastien
// Do not run on production or staging. Local dev only

#[AsCommand(
    name: 'app:rpps:detect-duplicates',
    description: 'Scan the hardcoded RPPS CSV and write a CSV with duplicate lines (incl. original for first duplicate)'
)]
class RppsDetectDuplicates extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Hardcoded paths and settings
        $inputPath = 'var/rpps/PS_LibreAcces_Personne_activite_202406110803.txt';
        $outputPath = 'var/rpps/duplicates_test_100k.csv';
        $delimiter = '|';
        $progressEvery = 500;
        $hardLimit = 100000; // data lines (excludes header)

        if (!is_file($inputPath)) {
            $output->writeln("<error>File not found: {$inputPath}</error>");

            return Command::FAILURE;
        }

        try {
            $in = new SplFileObject($inputPath, 'r');
            $in->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        } catch (Throwable $e) {
            $output->writeln("<error>Cannot open file: {$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        // Open output CSV
        $outHandle = @fopen($outputPath, 'w');
        if (false === $outHandle) {
            $output->writeln("<error>Cannot open output file for writing: {$outputPath}</error>");

            return Command::FAILURE;
        }

        // Parse header
        $header = $in->fgetcsv($delimiter);
        if (false === $header) {
            fclose($outHandle);
            $output->writeln('<error>Empty file or unreadable header.</error>');

            return Command::FAILURE;
        }

        // Write header to output
        fputcsv($outHandle, $header, $delimiter);

        // Find needed columns
        $headerIndex = array_flip($header);
        $colRaw = $headerIndex['Identifiant PP'] ?? null;
        $colNational = $headerIndex['Identification nationale PP'] ?? null;

        if (null === $colRaw && null === $colNational) {
            fclose($outHandle);
            $output->writeln('<error>Required columns not found in header.</error>');

            return Command::FAILURE;
        }

        $counts = [];         // key => count
        $firstLines = [];     // key => first line (array of columns)
        $linesRead = 0;       // data lines read (excluding header)
        $linesWritten = 0;    // lines written to output (including header already written)
        $dupKeys = 0;         // number of keys with count >= 2
        $tripKeys = 0;        // number of keys with count >= 3

        while (!$in->eof()) {
            /* @phpstan-ignore-next-line */
            if ($hardLimit > 0 && $linesRead >= $hardLimit) {
                break;
            }

            $row = $in->fgetcsv($delimiter);
            if (false === $row || $row === [null]) {
                continue;
            }

            ++$linesRead;

            // Extract values
            $raw = null !== $colRaw && array_key_exists($colRaw, $row) ? trim((string) $row[$colRaw]) : '';
            $national = null !== $colNational && array_key_exists($colNational, $row) ? trim((string) $row[$colNational]) : '';

            // Key: use national, fallback to padded raw
            $key = '' !== $national ? $national : ('' !== $raw ? str_pad($raw, 10, '0', STR_PAD_LEFT) : '');

            if ('' === $key) {
                if (0 === $linesRead % $progressEvery) {
                    $output->writeln("Processed {$linesRead} lines... (empty key)");
                }
                continue;
            }

            $currentCount = $counts[$key] ?? 0;

            if (0 === $currentCount) {
                // First occurrence: remember the line, do not write yet
                $firstLines[$key] = $row;
                $counts[$key] = 1;
            } elseif (1 === $currentCount) {
                // Second occurrence: write the first occurrence and this one
                if (isset($firstLines[$key])) {
                    fputcsv($outHandle, $firstLines[$key], $delimiter);
                    ++$linesWritten;
                    unset($firstLines[$key]); // free memory
                }
                fputcsv($outHandle, $row, $delimiter);
                ++$linesWritten;
                $counts[$key] = 2;
                ++$dupKeys; // first time we cross to duplicate for this key
            } else {
                // Third or more: write only the current row
                fputcsv($outHandle, $row, $delimiter);
                ++$linesWritten;

                // Count keys that reached triple+ exactly once
                if (2 === $currentCount) {
                    ++$tripKeys;
                }
                $counts[$key] = $currentCount + 1;
            }

            if (0 === $linesRead % $progressEvery) {
                $output->writeln("Processed {$linesRead} lines... written: {$linesWritten}");
            }
        }

        fclose($outHandle);

        $output->writeln("Done. Data lines read (limited): {$linesRead}. Lines written: {$linesWritten}. Keys with duplicates (>=2): {$dupKeys}. Keys with triple or more (>=3): {$tripKeys}. Output: {$outputPath}");

        return Command::SUCCESS;
    }
}
