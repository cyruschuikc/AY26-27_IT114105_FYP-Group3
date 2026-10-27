<?php

function topicModeLabel($mode)
{
    $labels = array(
        'Fixed' => 'Fixed topic',
        'Multi' => 'Multi-select topics',
        'Self'  => 'Self-selected topic',
    );

    return isset($labels[$mode]) ? $labels[$mode] : $mode;
}

function topicModeDescription($mode)
{
    $descriptions = array(
        'Fixed' => 'Every team uses the single topic supplied by the teacher.',
        'Multi' => 'The teacher supplies topic options and each team leader selects one for approval.',
        'Self'  => 'Each team leader proposes an original topic for teacher approval.',
    );

    return isset($descriptions[$mode]) ? $descriptions[$mode] : '';
}

function topicProposalStatusLabel($status)
{
    $labels = array(
        'Pending'  => 'Pending approval',
        'Approved' => 'Approved',
        'Rejected' => 'Revision required',
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
}

function latestTeamTopicProposal($conn, $groupId)
{
    $stmt = mysqli_prepare($conn, "
        SELECT tp.*, pt.title AS selected_topic_title
        FROM `team_topic_proposals` tp
        LEFT JOIN `project_topics` pt ON pt.id = tp.topic_id
        WHERE tp.group_id = ?
        ORDER BY tp.version DESC, tp.id DESC
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "i", $groupId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $proposal = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    return $proposal ?: null;
}

function topicSelectionSummary($conn, $projectId, $topicId)
{
    $summary = array(
        'teams' => 0,
        'students' => 0,
        'pending_teams' => 0,
        'approved_teams' => 0,
        'pending_students' => 0,
        'approved_students' => 0,
    );

    $stmt = mysqli_prepare($conn, "
        SELECT
            COUNT(*) AS teams,
            COALESCE(SUM(mc.member_count), 0) AS students,
            SUM(CASE WHEN tp.status = 'Pending' THEN 1 ELSE 0 END) AS pending_teams,
            SUM(CASE WHEN tp.status = 'Approved' THEN 1 ELSE 0 END) AS approved_teams,
            COALESCE(SUM(CASE WHEN tp.status = 'Pending' THEN mc.member_count ELSE 0 END), 0) AS pending_students,
            COALESCE(SUM(CASE WHEN tp.status = 'Approved' THEN mc.member_count ELSE 0 END), 0) AS approved_students
        FROM `team_topic_proposals` tp
        INNER JOIN `groups` g ON g.id = tp.group_id
        INNER JOIN (
            SELECT group_id, COUNT(*) AS member_count
            FROM `group_members`
            GROUP BY group_id
        ) mc ON mc.group_id = tp.group_id
        WHERE tp.project_id = ?
          AND tp.topic_id = ?
          AND tp.status IN ('Pending', 'Approved')
          AND tp.version = (
              SELECT MAX(tp2.version)
              FROM `team_topic_proposals` tp2
              WHERE tp2.group_id = tp.group_id
          )
    ");
    mysqli_stmt_bind_param($stmt, "ii", $projectId, $topicId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if ($row) {
        foreach ($summary as $key => $value) {
            $summary[$key] = (int) $row[$key];
        }
    }

    return $summary;
}

function activeProjectTopics($conn, $projectId)
{
    $topics = array();
    $stmt = mysqli_prepare($conn, "
        SELECT id, title, description, position
        FROM `project_topics`
        WHERE project_id = ? AND is_active = 1
        ORDER BY position, id
    ");
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $topics[] = $row;
    }
    mysqli_stmt_close($stmt);

    return $topics;
}
