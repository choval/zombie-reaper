<?php

function zombie_find(?int $ppid=null)
{
    if (is_null($ppid)) {
        $ppid = getmypid();
    }
    $cmd = 'ps ax -o stat,pid,ppid,time,comm|awk \'($1 ~ /Z/) && ($3=="'.$ppid.'")\'';
    exec($cmd, $lines);
    $zombies = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $parts = preg_split('/[\s]+/', $line, 5);
        $zombie = [
            'stat' => $parts[0],
            'pid' => (int)$parts[1],
            'ppid' => (int)$parts[2],
            'time' => $parts[3],
            'comm' => $parts[4],
        ];
        $zombies[] = $zombie;
    }
    return $zombies;
}

function zombie_kill(int $pid, int $signal=9): int
{
    $signaled = posix_kill($pid, $signal);
    if ($signaled) {
        return zombie_reap($pid);
    }
    return -1;
}

function zombie_reap(int $pid): int
{
    return pcntl_waitpid($pid, $status, \WNOHANG);
}

function zombie_kill_all(?array $pids=null, int $signal=9): array
{
    if (is_null($pids)) {
        $zombies = zombie_find();
        $pids = [];
        foreach ($zombies as $zombie) {
            $pids[] = $zombie['pid'];
        }
    }
    $result = [];
    foreach ($pids as $pid) {
        $pid = $pid['pid'] ?? $pid;
        $result[$pid] = zombie_kill($pid, $signal);
    }
    return $result;
}

function zombie_reap_all(?array $pids=null): array
{
    if (is_null($pids)) {
        $zombies = zombie_find();
        $pids = [];
        foreach ($zombies as $zombie) {
            $pids[] = $zombie['pid'];
        }
    }
    $result = [];
    foreach ($pids as $pid) {
        $pid = $pid['pid'] ?? $pid;
        $result[$pid] = zombie_reap($pid);
    }
    return $result;
}
