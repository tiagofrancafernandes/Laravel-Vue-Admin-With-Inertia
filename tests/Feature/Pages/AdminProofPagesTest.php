<?php

namespace Tests\Feature\Pages;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\ClientLedger;
use App\Models\ClientProof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProofPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $clientUser;
    protected User $attendantUser;
    protected Client $client;
    protected ClientBalance $clientBalance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user (super_admin)
        $this->adminUser = User::factory()->create(['type' => 'super_admin']);

        // Create attendant user
        $this->attendantUser = User::factory()->create(['type' => 'attendant']);

        // Create client user
        $this->clientUser = User::factory()->create(['type' => 'client']);

        // Create corresponding client with same ID as user
        $this->client = Client::factory()->create([
            'id' => $this->clientUser->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '(11) 99999-9999',
        ]);

        // Create client balance
        $this->clientBalance = $this->client->balance()->create([
            'balance' => 1000.00,
            'credit_limit' => 5000.00,
        ]);
    }

    // ==================== Authorization Tests ====================

    public function testUnauthenticatedUserRedirectedToLoginForProofsList(): void
    {
        $response = $this->get(route('admin.proofs.index'));

        $response->assertRedirect(route('login'));
    }

    public function testAttendantUserCannotAccessProofsList(): void
    {
        $response = $this->actingAs($this->attendantUser)
            ->get(route('admin.proofs.index'));

        $response->assertForbidden();
    }

    public function testClientUserCannotAccessProofsList(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('admin.proofs.index'));

        $response->assertForbidden();
    }

    public function testAdminCanAccessProofsList(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        // Authorization check: should not be forbidden
        $this->assertNotEquals(403, $response->status());
    }

    public function testUnauthenticatedUserRedirectedToLoginForProofDetail(): void
    {
        $proof = ClientProof::factory()->create();

        $response = $this->get(route('admin.proofs.show', $proof->id));

        $response->assertRedirect(route('login'));
    }

    public function testAttendantUserCannotAccessProofDetail(): void
    {
        $proof = ClientProof::factory()->create();

        $response = $this->actingAs($this->attendantUser)
            ->get(route('admin.proofs.show', $proof->id));

        $response->assertForbidden();
    }

    public function testAdminCanAccessProofDetail(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.show', $proof->id));

        // Authorization check: should not be forbidden
        $this->assertNotEquals(403, $response->status());
    }

    // ==================== Proofs List Tests ====================

    public function testAdminProofIndexRouteExists(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        // Route exists and admin can access it (not forbidden)
        $this->assertNotEquals(403, $response->status());
    }

    // ==================== Approval Tests ====================

    public function testAdminCanApproveDepositProof(): void
    {
        Storage::fake('private');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 300.00,
            'status' => 'pending',
            'file_path' => 'client-proofs/100/test.pdf',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => 'Verified and approved',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('client_proofs', [
            'id' => $proof->id,
            'status' => 'approved',
            'admin_id' => $this->adminUser->id,
            'notes' => 'Verified and approved',
        ]);
    }

    public function testApprovingDepositProofIncreasesClientBalance(): void
    {
        Storage::fake('private');

        $initialBalance = $this->clientBalance->balance;

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => 'Approved',
            ]);

        $this->clientBalance->refresh();
        $this->assertEquals($initialBalance + 500.00, $this->clientBalance->balance);
    }

    public function testApprovingDepositProofCreatesLedgerEntry(): void
    {
        Storage::fake('private');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => 'Approved',
            ]);

        // Verify that approval was processed
        $this->assertDatabaseHas('client_proofs', [
            'id' => $proof->id,
            'status' => 'approved',
        ]);

        // Verify ledger entry was created
        $ledgerCount = ClientLedger::where('client_id', $this->client->id)
            ->where('type', 'credit')
            ->where('amount', 500.00)
            ->count();
        $this->assertGreaterThan(0, $ledgerCount);
    }

    public function testApprovingPaymentProofDoesNotChangeBalance(): void
    {
        Storage::fake('private');

        $initialBalance = $this->clientBalance->balance;

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'payment',
            'amount' => 200.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => 'Payment verified',
            ]);

        $this->clientBalance->refresh();
        $this->assertEquals($initialBalance, $this->clientBalance->balance);
    }

    public function testApprovalNotesAreSaved(): void
    {
        Storage::fake('private');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        $testNotes = 'This proof is valid and matches the transaction';

        $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => $testNotes,
            ]);

        $this->assertDatabaseHas('client_proofs', [
            'id' => $proof->id,
            'notes' => $testNotes,
        ]);
    }

    public function testAttendantCannotApproveProof(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->attendantUser)
            ->post(route('admin.proofs.approve', $proof->id), [
                'notes' => 'Approved',
            ]);

        $response->assertForbidden();
    }

    // ==================== Rejection Tests ====================

    public function testAdminCanRejectProof(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.reject', $proof->id), [
                'notes' => 'Invalid document',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('client_proofs', [
            'id' => $proof->id,
            'status' => 'rejected',
            'admin_id' => $this->adminUser->id,
            'notes' => 'Invalid document',
        ]);
    }

    public function testRejectingProofRequiresNotes(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.reject', $proof->id), [
                'notes' => '',
            ]);

        $response->assertSessionHasErrors('notes');
    }

    public function testRejectingProofDoesNotChangeBalance(): void
    {
        $initialBalance = $this->clientBalance->balance;

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('admin.proofs.reject', $proof->id), [
                'notes' => 'Insufficient evidence',
            ]);

        $this->clientBalance->refresh();
        $this->assertEquals($initialBalance, $this->clientBalance->balance);
    }

    public function testAttendantCannotRejectProof(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->attendantUser)
            ->post(route('admin.proofs.reject', $proof->id), [
                'notes' => 'Rejected',
            ]);

        $response->assertForbidden();
    }

    // ==================== File Download Tests ====================

    public function testAdminCanDownloadProofFile(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('client-proofs/100/test.pdf', 'test content');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'file_path' => 'client-proofs/100/test.pdf',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.download', $proof->id));

        $response->assertStatus(200);
    }

    public function testDownloadProofReturns404ForMissingFile(): void
    {
        Storage::fake('private');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'file_path' => 'client-proofs/100/nonexistent.pdf',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.download', $proof->id));

        $response->assertNotFound();
    }

    public function testAttendantCannotDownloadProofFile(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('client-proofs/100/test.pdf', 'test content');

        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'file_path' => 'client-proofs/100/test.pdf',
        ]);

        $response = $this->actingAs($this->attendantUser)
            ->get(route('admin.proofs.download', $proof->id));

        $response->assertForbidden();
    }
}
