<?php
require_once __DIR__ . '/../model.php';

class VacataireAffectationModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Affecte un module à un vacataire.
     */
    public function assignModuleToVacataire($vacataireId, $coordId, $moduleId, $annee)
    {
        $query = "INSERT INTO affectation_vacataire (to_vacataire, by_coordonnateur, id_module, annee) 
                  VALUES (?, ?, ?, ?)";
        return $this->db->query($query, [$vacataireId, $coordId, $moduleId, $annee]);
    }
    public function countModulesByVacataire($id_user): int
    {
        $query = "SELECT COUNT(*) as total FROM affectation_vacataire WHERE to_vacataire = ?";
        $this->db->query($query, [$id_user]);
        $result = $this->db->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    public function getModulesByVacataire($vacataireId)
    {
        $query = "SELECT m.title, m.code_module, a.annee
              FROM affectation_vacataire a
              JOIN module m ON a.id_module = m.id_module
              WHERE a.to_vacataire = ?
              ORDER BY a.annee DESC";
        $this->db->query($query, [$vacataireId]);
        return $this->db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDistinctVacatairesByCoord($coordId)
    {
        $sql = "SELECT COUNT(DISTINCT to_vacataire) as total FROM affectation_vacataire WHERE by_coordonnateur = ?";
        $this->db->query($sql, [$coordId]);
        return (int)$this->db->fetch()['total'] ?? 0;
    }

    public function countAffectationsByCoord($coordId)
    {
        $sql = "SELECT COUNT(*) as total FROM affectation_vacataire WHERE by_coordonnateur = ?";
        $this->db->query($sql, [$coordId]);
        return (int)$this->db->fetch()['total'] ?? 0;
    }



    public function getModulesByVacataireId($vacataireId)
    {
        $query = "
        SELECT 
            m.title, m.code_module, m.semester, m.credits,
            m.volume_cours, m.volume_td, m.volume_tp, m.volume_autre,
            av.annee
        FROM affectation_vacataire av
        JOIN module m ON av.id_module = m.id_module
        WHERE av.to_vacataire = ?
        ORDER BY av.annee DESC, m.code_module ASC
    ";
        $this->db->query($query, [$vacataireId]);
        return $this->db->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retourne les affectations faites par ce coordonnateur.
     */
    public function getAssignedModulesByCoord($coordId)
    {
        $query = "SELECT av.*, 
                         m.title AS module_title, 
                         m.code_module, 
                         u.firstName, 
                         u.lastName
                  FROM affectation_vacataire av
                  JOIN module m ON av.id_module = m.id_module
                  JOIN user u ON av.to_vacataire = u.id_user
                  WHERE av.by_coordonnateur = ?
                  ORDER BY av.annee DESC";

        $this->db->query($query, [$coordId]);
        return $this->db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste tous les vacataires disponibles.
     */
    public function getAvailableVacataires()
    {
        $query = "SELECT id_user, CONCAT(firstName, ' ', lastName) AS name 
                  FROM user 
                  WHERE role = 'vacataire'";
        $this->db->query($query);
        return $this->db->fetchAll(PDO::FETCH_ASSOC);
    }
}
