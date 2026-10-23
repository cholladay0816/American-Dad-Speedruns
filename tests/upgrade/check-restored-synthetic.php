<?php
$pdo=new PDO('sqlite:/app/tests/baseline/artifacts/rehearsal/restored-legacy-copy.sqlite',null,null,[Pdo\Sqlite::ATTR_OPEN_FLAGS=>Pdo\Sqlite::OPEN_READONLY]);
$result=['syntheticOnly'=>true,'integrity'=>$pdo->query('PRAGMA integrity_check')->fetchColumn(),'foreignKeyViolations'=>count($pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_NUM)),'tables'=>(int)$pdo->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchColumn(),'oldRuntimeBootVerified'=>false,'productionRestoreVerified'=>false];
file_put_contents('/app/tests/baseline/artifacts/rehearsal/restored-integrity.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result),PHP_EOL;
