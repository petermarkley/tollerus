<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Services;

use PeterMarkley\Tollerus\Enums\FontFormat;
use PeterMarkley\Tollerus\Models\Neography;

final class FontAssetService
{
    /**
     * Copy font from database blob to a public asset
     */
    public function publish(FontFormat $format, Neography $neography): void
    {
        // Sanity check
        if (empty($neography->{$format->blobColumn()})) {
            throw new \RuntimeException(__('tollerus::error.font_missing'));
        }

        // Make sure folder is ready
        $folderName = 'vendor/tollerus/fonts';
        $folderPath = public_path($folderName);
        if (file_exists($folderPath)) {
            if (!is_dir($folderPath)) {
                throw new \RuntimeException(__('tollerus::error.folder_conflict'));
            }
        } else {
            mkdir($folderPath);
        }

        // Get path & URL
        $fileName = $neography->machine_name.'.'.$format->extension();
        $filePath = public_path($folderName . '/' . $fileName);
        $assetUrl = '/' . $folderName . '/' . $fileName;
        if (file_exists($filePath)) {
            throw new \RuntimeException(__('tollerus::error.file_conflict'));
        }

        // Publish & save
        file_put_contents($filePath, $neography->{$format->blobColumn()});
        $neography->{$format->pathColumn()} = $filePath;
        $neography->{$format->urlColumn()} = $assetUrl;
        $neography->save();
    }

    /**
     * Remove public font asset
     */
    public function delete(FontFormat $format, Neography $neography): void
    {
        // Sanity check
        $file = $neography->{$format->pathColumn()};
        if (empty($file)) {
            throw new \RuntimeException(__('tollerus::error.file_path_missing'));
        }
        if (!file_exists($file) || !is_file($file)) {
            throw new \RuntimeException(__('tollerus::error.file_missing'));
        }

        // Delete file
        unlink($file);

        // Update model
        $neography->{$format->pathColumn()} = null;
        $neography->{$format->urlColumn()} = null;
        $neography->save();
    }

    /**
     * Convenience function
     */
    public function refresh(FontFormat $format, Neography $neography): void
    {
        $this->delete($format, $neography);
        $this->publish($format, $neography);
    }
}