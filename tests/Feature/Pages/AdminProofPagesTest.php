<?php

namespace Tests\Feature\Pages;

use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\ClientProof;
use App\Models\Sale;
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

        // Create client user with matching client ID
        $this->clientUser = User::factory()->create([
            'id' => 100,
            'type' => 'client',
        ]);

        // Create corresponding client
        $this->client = Client::factory()->create([
            'id' => 100,
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

        $response->assertStatus(200);
        $response->assertViewIs('Admin/ProofsList');
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

        $response->assertStatus(200);
        $response->assertViewIs('Admin/ProofDetail');
        $response->assertViewHasAll(['proof']);
    }

    // ==================== Proofs List Tests ====================

    public function testAdminCanViewAllProofs(): void
    {
        $proofs = ClientProof::factory(3)->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('proofs');

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(3, $proofsData);
    }

    public function testProofsListShowsCorrectProofData(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        $proofsData = $response->props['proofs']['data'];
        $proofData = $proofsData[0];

        $this->assertEquals($proof->id, $proofData['id']);
        $this->assertEquals('deposit', $proofData['type']);
        $this->assertEquals(500.00, $proofData['amount']);
        $this->assertEquals('pending', $proofData['status']);
    }

    public function testProofsListIncludesClientData(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        $proofsData = $response->props['proofs']['data'];
        $proofData = $proofsData[0];

        $this->assertEquals($this->client->name, $proofData['client']['name']);
        $this->assertEquals($this->client->email, $proofData['client']['email']);
    }

    public function testProofsListPaginationWorks(): void
    {
        // Create more proofs than page limit (20)
        ClientProof::factory(25)->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index'));

        $proofs = $response->props['proofs'];
        $this->assertEquals(20, count($proofs['data']));
        $this->assertEquals(2, $proofs['last_page']);
        $this->assertEquals(1, $proofs['current_page']);
    }

    // ==================== Filter Tests ====================

    public function testFilterByStatusPending(): void
    {
        ClientProof::factory()->create(['client_id' => $this->client->id, 'status' => 'pending']);
        ClientProof::factory()->create(['client_id' => $this->client->id, 'status' => 'approved']);
        ClientProof::factory()->create(['client_id' => $this->client->id, 'status' => 'rejected']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['status' => 'pending']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals('pending', $proofsData[0]['status']);
    }

    public function testFilterByStatusApproved(): void
    {
        ClientProof::factory()->create(['client_id' => $this->client->id, 'status' => 'pending']);
        ClientProof::factory()->create(['client_id' => $this->client->id, 'status' => 'approved']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['status' => 'approved']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals('approved', $proofsData[0]['status']);
    }

    public function testFilterByTypeDeposit(): void
    {
        ClientProof::factory()->create(['client_id' => $this->client->id, 'type' => 'deposit']);
        ClientProof::factory()->create(['client_id' => $this->client->id, 'type' => 'payment']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['type' => 'deposit']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals('deposit', $proofsData[0]['type']);
    }

    public function testFilterByTypePayment(): void
    {
        ClientProof::factory()->create(['client_id' => $this->client->id, 'type' => 'deposit']);
        ClientProof::factory()->create(['client_id' => $this->client->id, 'type' => 'payment']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['type' => 'payment']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals('payment', $proofsData[0]['type']);
    }

    public function testSearchByClientName(): void
    {
        $otherClient = Client::factory()->create(['name' => 'Other Client']);
        ClientProof::factory()->create(['client_id' => $this->client->id]);
        ClientProof::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['search' => 'Test Client']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals($this->client->name, $proofsData[0]['client']['name']);
    }

    public function testSearchByClientEmail(): void
    {
        $otherClient = Client::factory()->create(['email' => 'other@example.com']);
        ClientProof::factory()->create(['client_id' => $this->client->id]);
        ClientProof::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.index', ['search' => 'client@example.com']));

        $proofsData = $response->props['proofs']['data'];
        $this->assertCount(1, $proofsData);
        $this->assertEquals($this->client->email, $proofsData[0]['client']['email']);
    }

    // ==================== Proof Detail Tests ====================

    public function testProofDetailShowsCorrectData(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'deposit',
            'amount' => 250.50,
            'status' => 'pending',
            'file_path' => 'client-proofs/100/test.pdf',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.show', $proof->id));

        $proofData = $response->props['proof'];
        $this->assertEquals($proof->id, $proofData['id']);
        $this->assertEquals('deposit', $proofData['type']);
        $this->assertEquals(250.50, $proofData['amount']);
        $this->assertEquals('pending', $proofData['status']);
    }

    public function testProofDetailIncludesClientInfo(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.show', $proof->id));

        $proofData = $response->props['proof'];
        $this->assertEquals($this->client->id, $proofData['client']['id']);
        $this->assertEquals($this->client->name, $proofData['client']['name']);
        $this->assertEquals($this->client->email, $proofData['client']['email']);
        $this->assertEquals($this->client->phone, $proofData['client']['phone']);
    }

    public function testProofDetailShowsAdminInfoIfApproved(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'approved',
            'admin_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.show', $proof->id));

        $proofData = $response->props['proof'];
        $this->assertNotNull($proofData['admin']);
        $this->assertEquals($this->adminUser->id, $proofData['admin']['id']);
        $this->assertEquals($this->adminUser->name, $proofData['admin']['name']);
    }

    public function testProofDetailShowsSaleInfo(): void
    {
        $sale = Sale::factory()->create(['user_id' => $this->attendantUser->id]);
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'sale_id' => $sale->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.proofs.show', $proof->id));

        $proofData = $response->props['proof'];
        $this->assertNotNull($proofData['sale']);
        $this->assertEquals($sale->id, $proofData['sale']['id']);
        $this->assertEquals($sale->code, $proofData['sale']['code']);
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

        $this->assertDatabaseHas('client_ledgers', [
            'client_id' => $this->client->id,
            'type' => 'credit',
            'amount' => 500.00,
        ]);
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
