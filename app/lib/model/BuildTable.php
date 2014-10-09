<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 07.08.14
 * Time: 13:58
 */

namespace Builder\Model;

class BuildTable
{
    const MONGO_FIELD_NAME_ID = '_id';
    const MONGO_FIELD_NAME_CREATED_TIME = 'created_at';
    const MONGO_FIELD_NAME_UPDATED_TIME = 'updated_at';

    private $db;

    private $config;

    private $collection_name = 'builds';

    /**
     * Structure of data that this Model can hold and export.
     *
     * ID and date are always shown by default. Its hardcoded.
     */
    private $fields_structure = [
        'name' => [
            'required' => true,
            'export' => true
        ],
        'version' => [
            'required' => true,
            'export' => true
        ],
        'build' => [
            'required' => true,
            'export' => true
        ],
        'build_filename' => [
            'required' => false,
            'export' => false
        ],
        'slug' => [
            'required' => true,
            'export' => true
        ],
        'travis_build_id' => [
            'required' => false,
            'export' => true
        ],
        'travis_build_number' => [
            'required' => false,
            'export' => true
        ],
        'travis_job_id' => [
            'required' => false,
            'export' => true
        ],
        'travis_job_number' => [
            'required' => false,
            'export' => true
        ],
        'branch' => [
            'required' => true,
            'export' => true
        ],
        'commit' => [
            'required' => true,
            'export' => true
        ],
        'commit_range' => [
            'required' => false,
            'export' => true
        ],
        'bundle' => [
            'required' => false,
            'export' => true
        ],
        'server_id' => [
            'required' => false,
            'export' => true
        ],
        'comment' => [
            'required' => false,
            'export' => true
        ],
    ];

    public function __construct(\MongoDb $db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function search($search, $limit = 30, $offset = 0)
    {
        $cursor = $this->getCollection()
            ->find()
            ->skip($offset)
            ->limit($limit)
            ->sort([self::MONGO_FIELD_NAME_CREATED_TIME => -1, self::MONGO_FIELD_NAME_ID => 1]);

        if ($toJson) {
            $cursor = $this->toJson($cursor);
        }

        return $cursor;
    }

    public function getList($limit = 30, $offset = 0, $toJson = false)
    {
        $cursor = $this->getCollection()
            ->find()
            ->skip($offset)
            ->limit($limit)
            ->sort([self::MONGO_FIELD_NAME_CREATED_TIME => -1, self::MONGO_FIELD_NAME_ID => 1]);

        if ($toJson) {
            $cursor = $this->toJson($cursor);
        }

        return $cursor;
    }

    public function getById($id)
    {
        if (!is_object($id)) {
            $id = new \MongoId($id);
        }

        return $this->getCollection()->findOne([self::MONGO_FIELD_NAME_ID => $id]);
    }

    public function create($data, array $file)
    {
        $response = [
            'code' => 500,
            'error' => 'Internal error',
            'data' => null,
        ];

        $allowed_fields = $this->getFieldsStructure();

        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $allowed_fields)) {
                unset($data[$field]);
            }
        }

        if (count($data) < 1) {
            $response['code'] = 400;
            $response['error'] = 'No data found';

            return $response;
        }

        $record_id = $data[self::MONGO_FIELD_NAME_ID] = new \MongoId;
        $data[self::MONGO_FIELD_NAME_CREATED_TIME] = new \MongoDate(time());
        $data['build_filename'] = $file['name'];

        $insert_result = $this->getCollection()->insert($data);
        if (1 != $insert_result['ok']) {
            $response['error'] = $result['err'];

            return $response;
        }

        $build_dir = $this->getBuildDirById($data[self::MONGO_FIELD_NAME_ID]);
        if (!mkdir($build_dir, 0777)) {
            $response['error'] = 'Failed create builds folder';

            return $response;
        }

        try {
            if (!move_uploaded_file($file['tmp_name'], $build_dir . $file['name'])) {
                rmdir($build_dir);
                $this->deleteById($record_id);
                $response['error'] = 'Failed to move uploaded file';

                return $response;
            }
        } catch (\Exception $e) {
            $this->deleteById($record_id);
            $response['error'] = "Cannot upload file with: '{$e->getMessage()}'";
        }

        try {
            if (!$this->generatePlist($data)) {
                $response['error'] = 'Failed to generate plist file';
            } else {
                $response['code'] = 200;
                unset($response['error']);
                $response['data'] = $this->toJson_Item($data);
            }
        } catch (\Exception $e) {
            $this->deleteById($record_id);
            $response['error'] = "Cannot generate plist file with: '{$e->getMessage()}'";
        }

        return $response;
    }

    public function getCollection()
    {
        return $this->db->selectCollection($this->getCollectionName());
    }

    public function getCollectionCount()
    {
        return $this->getCollection()->count();
    }

    public function getFieldsStructure()
    {
        return $this->fields_structure;
    }

    public function getValidatedFields()
    {
        return array_keys(array_filter($this->getFieldsStructure(), function ($item) {
            return $item['required'];
        }));
    }

    public function getCollectionName()
    {
        return $this->collection_name;
    }

    public function getBuildDirById($id)
    {
        return PROJECT_DIR . "www/builds/" . $id . DIRECTORY_SEPARATOR;
    }

    public function deleteById($id)
    {
        if (!is_object($id)) {
            $id = new \MongoId($id);
        }

        $record = $this->getById($id);
        if (is_null($record)) {
            return true;
        }

        $build_dir = $this->getBuildDirById($record[self::MONGO_FIELD_NAME_ID]);

        $build_ipa_file = $build_dir . $record['build_filename'];
        if (is_file($build_ipa_file)) {
            unlink($build_ipa_file);
        }

        $build_plist_file = $build_dir . $this->getPlistName($record);
        if (is_file($build_plist_file)) {
            unlink($build_plist_file);
        }

        if (is_dir($build_dir)) {
            rmdir($build_dir);
        }

        return $this->getCollection()->remove([self::MONGO_FIELD_NAME_ID => $id]);
    }

    public function toJson_Item($record, $available_fields = null)
    {
        if ($available_fields == null) {
            $available_fields = $this->getFieldsStructure();
        }

        $exported_record = [];
        $exported_record['id'] = (string) $record[self::MONGO_FIELD_NAME_ID];
        $exported_record['date'] = date('Y-m-d h:i:s', $record[self::MONGO_FIELD_NAME_CREATED_TIME]->sec);

        foreach ($available_fields as $field_name => $field_params) {
            if (!array_key_exists($field_name, $record) && !array_key_exists($field_name, $exported_record)) {
                $exported_record[$field_name] = null;
            } elseif ($field_params['export'] === true) {
                $exported_record[$field_name] = $record[$field_name];
            }
        }

        $exported_record['build_plist_url'] = $this->getPlistFileUrl($record);

        return $exported_record;
    }

    public function toJson(\MongoCursor $cursor)
    {
        $available_fields = $this->getFieldsStructure();

        $result = [];
        foreach ($cursor as $record) {
            $result[] = $this->toJson_Item($record, $available_fields);
        }

        return $result;
    }

    public function getPlistFileUrl($record)
    {
        return sprintf(
            'itms-services://?action=download-manifest&url=itms-services://?action=download-manifest&url=%s://%s/builds/%s/%s',
            $this->config->schema,
            $this->config->host,
            $record[self::MONGO_FIELD_NAME_ID],
            $this->getPlistName($record)
        );
    }

    public function getBuildFileUrl($record)
    {
        return sprintf(
            '%s://%s/builds/%s/%s',
            $this->config->schema,
            $this->config->host,
            $record[self::MONGO_FIELD_NAME_ID],
            $record['build_filename']
        );
    }

    private function getPlistName($record)
    {
        return str_replace(' ', '_', $record['name']) . ".plist";
    }

    private function generatePlist($record)
    {
        $xml = str_replace(
            ["{url}", "{bundle}", "{version}", "{name}",],
            [$this->getBuildFileUrl($record), $record['bundle'], $record['version'], $record['name'],],
            $this->getPlistXmlTemplate()
        );

        return (false !== file_put_contents(
            PROJECT_DIR . "www/builds/{$record[self::MONGO_FIELD_NAME_ID]}/{$this->getPlistName($record)}",
            $xml
        ));
    }

    private function getPlistXmlTemplate()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>{url}</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>{bundle}</string>
                <key>bundle-version</key>
                <string>{version}</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>{name}</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>

XML;
    }
}
