<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 07.08.14
 * Time: 13:58
 */

namespace Builder\Model;


use Klein\DataCollection\DataCollection;

class BuildTable
{
    const MONGO_FIELD_NAME_ID = '_id';
    const MONGO_FIELD_NAME_CREATED_TIME = 'created_at';
    const MONGO_FIELD_NAME_UPDATED_TIME = 'updated_at';

    private $db;

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

    public function __construct(\MongoDb $db)
    {
        $this->db = $db;
    }

    /**
     * @return \MongoCursor
     */
    public function getList($limit = 30, $offset = 0)
    {

        return $this->getCollection()
            ->find()
            ->skip($offset)
            ->limit($limit)
            ->sort([self::MONGO_FIELD_NAME_CREATED_TIME => 1, self::MONGO_FIELD_NAME_ID => 1]);
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

        $record = $this->getCollection()->insert($data);
        if (1 != $record['ok']) {
            $return['error'] = $record['err'];

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
        unset($fields['build_filename']);

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
} 