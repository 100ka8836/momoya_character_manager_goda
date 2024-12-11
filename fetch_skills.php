<?php
function fetchSkills($group_id, $pdo)
{
    $stmt = $pdo->prepare("
        SELECT cs.character_id, cs.skill_name, cs.skill_value
        FROM character_skills cs
        JOIN characters c ON cs.character_id = c.id
        WHERE c.group_id = ?
    ");
    $stmt->execute([$group_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $skills = [];
    $all_skills = [];
    foreach ($result as $row) {
        $skills[$row['character_id']][$row['skill_name']] = $row['skill_value'];
        if (!in_array($row['skill_name'], $all_skills)) {
            $all_skills[] = $row['skill_name'];
        }
    }

    return ['skills' => $skills, 'all_skills' => $all_skills];
}
