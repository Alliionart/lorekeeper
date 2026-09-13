<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;

class PluginController extends Controller {
    /**
     * Shows the Plugins index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPluginIndex(Request $request) {
        $path = base_path('plugins');

        $directories = File::directories($path);
        $pluginList = [];

        foreach ($directories as $directory) {
        }
        $folderName = basename($directory);
        $mdFilePath = $directory.'/'.$folderName.'.md';

        if (File::exists($mdFilePath)) {
            $content = File::get($mdFilePath);
            $pluginData = $this->parsePluginHeader($content);
            $pluginData['Folder'] = $folderName;
            $pluginsList[] = $pluginData;
        }

        return view('admin.plugins.index', [
            'plugins' => $pluginsList,
        ]);
    }

    /**
     * Reads the plugin's .md file header for information.
     */
    private function parsePluginHeader(string $content) {
        $headers = [
            'Plugin Name'   => 'plugin_name',
            'Version'       => 'version',
            'Author'        => 'author',
            'Author URL'    => 'author_url',
            'Description'   => 'description',
        ];

        $pluginData = [];

        foreach ($headers as $label => $key) {
            if (preg_match('/'.preg_quote($label).':\s*(.*)$/m', $content, $matches)) {
                $pluginData[$key] = trim($matches[1]);
            }
        }

        return $pluginData;
    }
}
