<?php

use App\Http\Controllers\WaController;
use App\Jobs\ProcessWaBlastCampaignJob;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WaBlastCampaign;
use App\Models\WaBlastLog;
use App\Models\WaBlastRecipient;
use App\Services\EvolutionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('user login and logout are recorded in activity_logs', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    // Test Login Event
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);
    $response->assertRedirect('/dashboard');

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'auth_login',
    ]);

    // Test Logout Event
    $response = $this->post('/logout');
    $response->assertRedirect('/');

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'auth_logout',
    ]);
});

test('failed login attempt is recorded in activity_logs', function () {
    $this->post('/login', [
        'email' => 'unknown@example.com',
        'password' => 'wrongpass',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'auth_failed',
    ]);
});

test('contact group and template CRUD are recorded in activity_logs', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create Template
    $this->post(route('wa.template.store'), [
        'judul' => 'Promo Spesial',
        'pesan' => 'Halo {nama}, dapatkan diskon!',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'template_create',
    ]);

    // Create Contact Group
    $this->post(route('wa.contact.store'), [
        'nama_grup' => 'Grup VIP',
        'nomor' => "Budi - 08123456789\nSiti - 08987654321",
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'contact_group_create',
    ]);
});

test('blast creates campaign, recipients, logs activity, and dispatches background queue job', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $targetInput = "Budi - 08123456789\nAni - 08555123456";
    $pesanInput = 'Halo {nama}, selamat datang!';

    $response = $this->postJson(route('wa.blast.start'), [
        'targets' => $targetInput,
        'pesan' => $pesanInput,
        'anti_bot' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'total_target' => 2,
        ]);

    $campaignId = $response->json('campaign_id');

    // Cek Campaign & Recipients di Database
    $this->assertDatabaseHas('wa_blast_campaigns', [
        'id' => $campaignId,
        'total_target' => 2,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('wa_blast_recipients', [
        'wa_blast_campaign_id' => $campaignId,
        'nomor' => '628123456789',
        'nama' => 'Budi',
        'pesan_personal' => 'Halo Budi, selamat datang!',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('wa_blast_recipients', [
        'wa_blast_campaign_id' => $campaignId,
        'nomor' => '628555123456',
        'nama' => 'Ani',
        'pesan_personal' => 'Halo Ani, selamat datang!',
        'status' => 'pending',
    ]);

    // Cek Activity Log
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'blast_created',
    ]);

    // Cek Queue Job ter-dispatch
    Queue::assertPushed(ProcessWaBlastCampaignJob::class, function ($job) use ($campaignId) {
        return $job->campaignId === $campaignId;
    });
});

test('user can view logs and campaign details', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Buka Halaman Log Aktivitas
    $response = $this->get(route('wa.logs'));
    $response->assertOk();

    // Buat Campaign dummy
    $campaign = WaBlastCampaign::create([
        'user_id' => $user->id,
        'judul' => 'Test Campaign',
        'pesan' => 'Testing pesan',
        'total_target' => 1,
        'status' => 'completed',
    ]);

    // Buka Halaman Rincian Campaign
    $response = $this->get(route('wa.blast.campaign.show', $campaign->id));
    $response->assertOk()
        ->assertSee('Test Campaign');
});

test('landing page renders successfully and shows branding', function () {
    AppSetting::set('site_name', 'Super Blast WA');

    $response = $this->get('/');
    $response->assertOk()
        ->assertSee('Super Blast WA')
        ->assertSee('Kirim Pesan WhatsApp Massal');
});

test('user can update website branding settings', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $logo = UploadedFile::fake()->image('custom_logo.png', 200, 200);
    $favicon = UploadedFile::fake()->image('custom_favicon.png', 32, 32);

    $response = $this->post(route('wa.setting.branding'), [
        'site_name' => 'BlastMaster Pro',
        'logo' => $logo,
        'favicon' => $favicon,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect(AppSetting::getSiteName())->toBe('BlastMaster Pro');
    expect(AppSetting::getLogoUrl())->not->toBeNull();
    expect(AppSetting::getFaviconUrl())->not->toBeNull();

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => 'branding_update',
    ]);
});

test('phone numbers in various formats (089, +6289, 89, 095, 6295, 95, scientific notation) are normalized to international format', function () {
    expect(EvolutionService::normalizePhoneNumber('0895350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('+62895350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('+62 895-3506-67734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('895350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('081234567890'))->toBe('6281234567890');

    // Perbaikan otomatis nomor Tri/Three yang kehilangan digit 8
    expect(EvolutionService::normalizePhoneNumber('095350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('6295350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('+6295350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('95350667734'))->toBe('62895350667734');
    expect(EvolutionService::normalizePhoneNumber('09612345678'))->toBe('6289612345678');

    // Notasi Ilmiah Excel
    expect(EvolutionService::normalizePhoneNumber('8.95351E+11'))->toBe('62895351000000');
    expect(EvolutionService::normalizePhoneNumber('6.28954E+13'))->toBe('62895400000000');
});

test('parseContactLine extracts name and phone correctly across diverse formats', function () {
    $controller = app(WaController::class);
    $method = new ReflectionMethod($controller, 'parseContactLine');
    $method->setAccessible(true);

    // 1. Paste dari Excel 2 kolom (Nama \t Nomor)
    $res1 = $method->invoke($controller, "tes\t0895350667734");
    expect($res1['nama'])->toBe('tes');
    expect($res1['nomor'])->toBe('62895350667734');

    // 2. Paste dari Excel 3 kolom dengan nomor urut (No \t Nama \t Nomor)
    $res2 = $method->invoke($controller, "1\ttes\t095350667734");
    expect($res2['nama'])->toBe('tes');
    expect($res2['nomor'])->toBe('62895350667734');

    // 3. Kolom terbalik (Nomor \t Nama)
    $res3 = $method->invoke($controller, "0895350667734\ttes");
    expect($res3['nama'])->toBe('tes');
    expect($res3['nomor'])->toBe('62895350667734');

    // 4. Header Excel diabaikan
    $res4 = $method->invoke($controller, "No\tNama\tNo HP");
    expect($res4['nomor'])->toBe('');

    // 5. Format CSV
    $res5 = $method->invoke($controller, 'tes, 0895350667734');
    expect($res5['nama'])->toBe('tes');
    expect($res5['nomor'])->toBe('62895350667734');

    // 6. Format nomor ber-strip dan tanda kurung
    $res6 = $method->invoke($controller, 'tes - 0895-3506-67734');
    expect($res6['nama'])->toBe('tes');
    expect($res6['nomor'])->toBe('62895350667734');

    $res7 = $method->invoke($controller, 'tes (0895350667734)');
    expect($res7['nama'])->toBe('tes');
    expect($res7['nomor'])->toBe('62895350667734');
});

test('user can update wa_instance_name and duplicate instance is rejected', function () {
    $user1 = User::factory()->create(['wa_instance_name' => 'instance_user1']);
    $user2 = User::factory()->create(['wa_instance_name' => 'instance_user2']);

    $this->actingAs($user2);

    // 1. Gagal jika mencoba memakai instance user1
    $response = $this->post(route('wa.setting.instance'), [
        'wa_instance_name' => 'instance_user1',
    ]);

    $response->assertSessionHasErrors(['wa_instance_name']);
    expect($user2->fresh()->wa_instance_name)->toBe('instance_user2');

    // 2. Berhasil jika memakai instance unik baru
    $responseSuccess = $this->post(route('wa.setting.instance'), [
        'wa_instance_name' => 'instance_user2_new',
    ]);

    $responseSuccess->assertRedirect()
        ->assertSessionHas('success');

    expect($user2->fresh()->wa_instance_name)->toBe('instance_user2_new');

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user2->id,
        'action' => 'instance_update',
    ]);
});

test('campaign completion logging is atomic and recorded exactly once per campaign', function () {
    $user = User::factory()->create(['wa_instance_name' => 'cs_instance']);

    $campaign = WaBlastCampaign::create([
        'user_id' => $user->id,
        'judul' => 'Campaign Test Multi Process',
        'pesan' => 'Halo {nama}',
        'total_target' => 1,
        'status' => 'processing',
    ]);

    WaBlastRecipient::create([
        'wa_blast_campaign_id' => $campaign->id,
        'nomor' => '628123456789',
        'nama' => 'Budi',
        'pesan_personal' => 'Halo Budi',
        'status' => 'sent',
    ]);

    $controller = app(WaController::class);

    // Panggilan 1 & Panggilan 2 memproses campaign yang sudah selesai penerimanya
    $controller->processNextRecipientForCampaign($campaign);
    $controller->processNextRecipientForCampaign($campaign);

    // WaBlastLog hanya boleh ada 1 catatan untuk campaign ini
    expect(WaBlastLog::where('user_id', $user->id)->count())->toBe(1);

    $log = WaBlastLog::where('user_id', $user->id)->first();
    expect($log->wa_instance)->toBe('cs_instance');
});
