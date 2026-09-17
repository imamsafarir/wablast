<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('group:diagnose {instance=admin_bpvp_pangkep}', function ($instance) {
    $evo = new \App\Services\EvolutionService(instanceName: $instance);
    $baseUrl = $evo->getBaseUrl();
    $apiKey = $evo->getApiKey();

    $this->info("Checking instance: $instance on $baseUrl");

    // 1. Check findChats
    $resChats = Illuminate\Support\Facades\Http::withHeaders(['apikey' => $apiKey])
        ->timeout(30)
        ->post("$baseUrl/chat/findChats/$instance", []);

    $chats = $resChats->json() ?? [];
    $this->info("findChats count: " . count($chats));

    $groupChats = [];
    foreach ($chats as $c) {
        $jid = $c['remoteJid'] ?? ($c['id'] ?? ($c['jid'] ?? ''));
        if (str_contains($jid, '@g.us')) {
            $groupChats[] = $c;
        }
    }
    $this->info("Group chats in findChats: " . count($groupChats));

    // 2. Check fetchAllGroups
    $this->info("Testing /group/fetchAllGroups/$instance?getParticipants=false ...");
    try {
        $start = microtime(true);
        $resAll = Illuminate\Support\Facades\Http::withHeaders(['apikey' => $apiKey])
            ->timeout(20)
            ->get("$baseUrl/group/fetchAllGroups/$instance?getParticipants=false");
        $duration = round(microtime(true) - $start, 2);
        $this->info("fetchAllGroups status: " . $resAll->status() . " (took {$duration}s)");
        if ($resAll->successful()) {
            $allGroups = $resAll->json() ?? [];
            $this->info("fetchAllGroups returned: " . count($allGroups) . " groups");
            foreach (array_slice($allGroups, 0, 5) as $g) {
                $this->line(" - " . ($g['subject'] ?? 'No subject') . " (" . ($g['id'] ?? '') . ")");
            }
        } else {
            $this->error("fetchAllGroups body: " . substr($resAll->body(), 0, 200));
        }
    } catch (\Throwable $e) {
        $this->error("fetchAllGroups failed: " . $e->getMessage());
    }

    // 3. Test findGroupInfos for a sample of groups in findChats
    $this->info("\nTesting findGroupInfos for first 15 groupChats...");
    foreach (array_slice($groupChats, 0, 15) as $g) {
        $jid = $g['remoteJid'] ?? ($g['id'] ?? ($g['jid'] ?? ''));
        $name = $g['pushName'] ?? ($g['subject'] ?? ($g['name'] ?? ''));
        $res = Illuminate\Support\Facades\Http::withHeaders(['apikey' => $apiKey])
            ->timeout(10)
            ->get("$baseUrl/group/findGroupInfos/$instance?groupJid=$jid");
        $this->line("JID: $jid | Name: $name | Status: " . $res->status() . " | Body: " . substr($res->body(), 0, 80));
    }
});
