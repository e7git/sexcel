<?php

namespace Sayhey\SExcel;

class Writer
{

    // 导出配置
    private static $config = [
        'publicDir' => '', // 项目公开目录
        'fileCacheTime' => 300, // 文件缓存时间，单位秒
        'autoClearFile' => true, // 是否自动清理文件
    ];

    /**
     * 设置导出配置
     * @param array $config
     * @return void
     */
    public static function config(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * 导出文件用于公开访问
     * @param array $rows 源数据，例如：[ ['id' => 1, 'name' => 'ABC' ], ['id' => 2, 'name' => 'DEF' ]...]
     * @param array $format 格式，例如：[ 'id' => ['业务ID', '0'], 'name' => '业务名']，其中键为源数据的键名，值数组的第一个值为列名，第二个值为格式，当默认字符串格式时值数组可以简写为字符串
     * @param string $filename 文件名，无路径无后缀，如：9月订单数据
     * @return [bool,string] 成功时返回：[ture, 文件路径]，失败时返回：[false, 报错内容]
     */
    public static function public(array $rows, array $format, string $filename = ''): array
    {
        // 判断源数据
        if (empty($rows)) {
            return [false, '导出结果为空'];
        }

        // 导出目录
        if ('' === self::$config['publicDir']) {
            return [false, '导出目录未设置'];
        }
        $publicDir = realpath(self::$config['publicDir']) . DIRECTORY_SEPARATOR;
        $tempDirName = 'sexcel-temp' . DIRECTORY_SEPARATOR;
        $tempDir = $publicDir . $tempDirName;
        $filename = basename($filename) . '-' . date('mdHis') . '.xlsx';

        // 构建表头
        $header = [];
        $columns = [];
        foreach ($format as $key => $value) {
            if (is_array($value)) {
                $header[$value[0]] = $value[1] ?? 'string';
            } else {
                $header[$value] = 'string';
            }
            $columns[] = $key;
        }
        unset($key, $value);

        // 导出
        try {
            if (!Util::mkdir($tempDir)) {
                return [false, '导出目录创建失败'];
            }

            $writer = new \XLSXWriter();
            $writer->writeSheetHeader('sheet1', $header);
            foreach ($rows as $value) {
                $row = [];
                foreach ($columns as $key) {
                    $row[] = $value[$key] ?? '';
                }
                $writer->writeSheetRow('sheet1', $row);
            }
            $writer->writeToFile($tempDir . $filename);

            unset($writer, $rows, $columns, $format, $row);

            // 清理目录
            self::clear();

            return [true, $publicDir . $filename];
        } catch (\Exception $exc) {
            return [false, '导出失败：' . $exc->getMessage()];
        }
    }

    /**
     * 清理文件
     * @param string $dir excel临时目录
     * @return void
     */
    private static function clear(string $dir): void
    {
        if (true !== self::$config['autoClearFile']) {
            return;
        }
        $cacheTime = max(300, intval(self::$config['fileCacheTime']));

        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            $file = $dir . $file;
            if ('.xlsx' !== substr($file, -4) || is_dir($file) || !is_file($file) || !is_readable($file) || !filemtime($file) || abs(time() - filemtime($file)) < $cacheTime) {
                continue;
            }
            Util::unlink($file);
        }
    }

}
