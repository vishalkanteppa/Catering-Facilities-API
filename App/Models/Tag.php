<?php

namespace App\Models;

use App\Plugins\Di\Injectable;

class Tag extends Injectable
{
    public function createTag(string $tagName): ?int
    {
        $query = "INSERT INTO tags (name) VALUES (:name);";
        $result = $this->db->executeQuery($query, ['name' => $tagName]);
        if ($result) {
            $tagId = (int) $this->db->executeQuery("SELECT LAST_INSERT_ID()")->fetchColumn();
            return $tagId;
        } else {
            // this would occur if an already existing tagName is tried to be inserted
            $query = "SELECT id FROM tags WHERE name = :name";
            $result = $this->db->executeQuery($query, ['name' => $tagName]);
            $tagId = $result->fetchColumn(); 
            return $tagId; // just return the id of the tag instead of trying to insert in this scenario
        }
    }

    public function getTagById(int $id): ?array
    {
        $query = "SELECT * FROM tags WHERE id = :id";
        $result = $this->db->executeQuery($query, ['id' => $id]);

        if ($result) {
            return $result->fetch(\PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function getAllTags(): array
    {
        $query = "SELECT * FROM tags";
        $result = $this->db->executeQuery($query);

        if (!$result) {
            return [];
        }
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTagsForFacility(int $facilityId): array
    {
        $query = "SELECT t.id, t.name 
                  FROM tags t
                  JOIN facility_tags ft ON t.id = ft.tag_id
                  WHERE ft.facility_id = :facility_id";
        $result = $this->db->executeQuery($query, ['facility_id' => $facilityId]);

        if (!$result) {
            return [];
        }
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateTag(string $oldName, string $newName) 
    {
        // checking if tag exists first
        $checkQuery = "SELECT COUNT(*) FROM tags WHERE name = :oldName";
        $result = $this->db->executeQuery($checkQuery, ['oldName' => $oldName]);

        if ($result && $result->rowCount() > 0) {
            $query = "UPDATE tags SET name = :newName WHERE name = :oldName";
            $this->db->executeQuery($query, ['newName' => $newName, 'oldName' => $oldName]);

            $query = "SELECT id from tags WHERE name = :newName";
            $tagId = $this->db->executeQuery($query, ['newName' => $newName])->fetch(\PDO::FETCH_ASSOC);
            // var_dump($tagId);
            return $tagId;
        }

        // if tag doesnt exist
        return false;
    }


    public function deleteTag(int $tagId): bool
    {
        // remove all associations from the facility_tags table first
        $deleteAssociationQuery = "DELETE FROM facility_tags WHERE tag_id = :tag_id";
        $this->db->executeQuery($deleteAssociationQuery, ['tag_id' => $tagId]);

        $deleteTagQuery = "DELETE FROM tags WHERE id = :id";
        return $this->db->executeQuery($deleteTagQuery, ['id' => $tagId]);
    }
}
