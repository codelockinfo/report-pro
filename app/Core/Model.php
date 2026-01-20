<?php

namespace App\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll($conditions = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Model::create failed for table {$this->table}: " . json_encode($errorInfo));
                error_log("SQL: {$sql}");
                error_log("Data: " . json_encode($data));
                return false;
            }

            $insertId = $this->db->lastInsertId();
            error_log("Model::create success for table {$this->table}, ID: {$insertId}");
            return $insertId;
        } catch (\Exception $e) {
            error_log("Model::create exception for table {$this->table}: " . $e->getMessage());
            error_log("Data: " . json_encode($data));
            return false;
        }
    }

    public function update($id, $data)
    {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $values[] = $id;

            $set = [];
            foreach ($fields as $field) {
                $set[] = "{$field} = ?";
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Model::update failed for table {$this->table}, ID {$id}: " . json_encode($errorInfo));
                error_log("SQL: {$sql}");
                error_log("Data: " . json_encode($data));
                return false;
            }
            
            error_log("Model::update success for table {$this->table}, ID: {$id}");
            return $result;
        } catch (\Exception $e) {
            error_log("Model::update exception for table {$this->table}, ID {$id}: " . $e->getMessage());
            error_log("Data: " . json_encode($data));
            return false;
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
}

