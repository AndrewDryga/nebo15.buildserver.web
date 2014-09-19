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

    private $collection_name = 'build';

    private $fields_structure = [
        'build',
        'build_filename',
        'branch',
        'repository',
        'name',
        'bundle',
        'version',
        'comment'
    ];

    public function __construct(\MongoDb $db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function getList($limit = 30, $offset = 0, $toJson = false)
    {
        $cursor = $this->getCollection()
            ->find()
            ->skip($offset)
            ->limit($limit)
            ->sort([self::MONGO_FIELD_NAME_CREATED_TIME => 1, self::MONGO_FIELD_NAME_ID => 1]);

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
        $return = [
            'success' => false,
            'code' => 500
        ];

        $allowed_fields = $this->getFieldsStructure();

        foreach ($data as $field => $value) {
            if (!in_array($field, $allowed_fields)) {
                unset($data[$field]);
            }
        }

        if (!$data) {
            $return['error'] = 'Nothing to save';

            return $return;
        }

        $record_id = $data[self::MONGO_FIELD_NAME_ID] = new \MongoId;
        $data[self::MONGO_FIELD_NAME_CREATED_TIME] = new \MongoDate(time());
        $data['build_filename'] = $file['name'];

        $result = $this->getCollection()->insert($data);
        if (1 != $result['ok']) {
            $return['error'] = $result['err'];

            return $return;
        }

        $build_dir = PROJECT_DIR . "public/builds/" . $data[self::MONGO_FIELD_NAME_ID] . DIRECTORY_SEPARATOR;
        mkdir($build_dir);

        try {
            if (!(move_uploaded_file($file['tmp_name'], $build_dir . $file['name']))) {
                rmdir($build_dir);
                $this->deleteById($record_id);
                $return['error'] = 'Failed upload file';
            } else {
                unset($return['code']);
                $return['success'] = true;
            }
        } catch (\Exception $e) {
            rmdir($build_dir);
            $this->deleteById($record_id);
            $return['error'] = "Cannot upload file with: '{$e->getMessage()}'";
        }

        $data[self::MONGO_FIELD_NAME_ID] = $record_id;
        $this->generatePlist($data);

        return $return;
    }

    public function getCollection()
    {
        return $this->db->selectCollection($this->getCollectionName());
    }

    public function getFieldsStructure()
    {
        return $this->fields_structure;
    }

    public function getValidatedFields()
    {
        $fields = $this->getFieldsStructure();
        unset($fields[array_search('build_filename', $fields)]);

        return $fields;
    }

    public function getCollectionName()
    {
        return $this->collection_name;
    }

    public function deleteById($id)
    {
        if (!is_object($id)) {
            $id = new \MongoId($id);
        }

        return $this->getCollection()->remove([self::MONGO_FIELD_NAME_ID => $id]);
    }

    public function toJson(\MongoCursor $cursor)
    {
        $arr = [];
        foreach ($cursor as $record) {

            $record_id = (string)$record[self::MONGO_FIELD_NAME_ID];

            $arr[$record_id] = [];

            foreach ($this->getFieldsStructure() as $field) {
                $arr[$record_id][$field] = $record[$field];
            }

            $arr[$record_id]['build_plist_url'] = $this->getPlistFileUrl($record);

        }

        return $arr;
    }

    public function getPlistFileUrl($record)
    {
        return sprintf(
            '://itms-services://?action=download-manifest&url=itms-services://?action=download-manifest&url=%s://%s/builds/%s/%s',
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

        file_put_contents(
            PROJECT_DIR . "public/builds/{$record[self::MONGO_FIELD_NAME_ID]}/{$this->getPlistName($record)}",
            $xml
        );
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
