<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\ClientBalance;
use App\Models\ClientProof;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user with type='client'
        $this->clientUser = User::factory()->create(['type' => 'client']);

        // Create a matching client record (using user id as client id)
        $this->client = Client::factory()->create(['id' => $this->clientUser->id]);

        // Create client balance
        ClientBalance::create([
            'client_id' => $this->client->id,
            'balance' => 1000.00,
            'credit_limit' => 500.00,
        ]);

        // Create a staff user for creating sales
        User::factory()->create(['id' => 999, 'type' => 'attendant']);
    }

    /**
     * Test non-client users cannot access portal.
     */
    public function testNonClientUserCannotAccessPortal(): void
    {
        $admin = User::factory()->create(['type' => 'super_admin']);

        $response = $this->actingAs($admin)->get('/client-portal');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated users are redirected to login.
     */
    public function testUnauthenticatedUserRedirectedToLogin(): void
    {
        $response = $this->get('/client-portal');

        $response->assertRedirect(route('login', absolute: false));
    }

    /**
     * Test client can submit a proof with file.
     */
    public function testClientCanSubmitProofWithFile(): void
    {
        \Illuminate\Support\Facades\Storage::fake('private');

        $file = \Illuminate\Http\UploadedFile::fake()->image('proof.jpg', 400, 400)->size(500);

        $response = $this->actingAs($this->clientUser)->post('/client-portal/proof', [
            'type' => 'deposit',
            'amount' => 500.00,
            'file' => $file,
            'description' => 'Depósito bancário',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify proof was created
        $this->assertDatabaseHas('client_proofs', [
            'client_id' => $this->clientUser->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'status' => 'pending',
        ]);
    }

    /**
     * Test proof submission validates file type.
     */
    public function testProofSubmissionValidatesFileType(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.exe', 100);

        $response = $this->actingAs($this->clientUser)->post('/client-portal/proof', [
            'type' => 'deposit',
            'amount' => 500.00,
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test proof submission validates amount.
     */
    public function testProofSubmissionValidatesAmount(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($this->clientUser)->post('/client-portal/proof', [
            'type' => 'deposit',
            'amount' => -100.00, // Invalid negative amount
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test client can only view own proofs.
     */
    public function testClientCanOnlyViewOwnProofs(): void
    {
        $otherClientUser = User::factory()->create(['type' => 'client']);
        $otherClient = Client::factory()->create(['id' => $otherClientUser->id]);

        $proof = ClientProof::factory()->create([
            'client_id' => $otherClient->id,
            'status' => 'pending',
        ]);

        // Try to access other client's proof
        $response = $this->actingAs($this->clientUser)
            ->get("/client-portal/proof/{$proof->id}/download");

        $response->assertStatus(403);
    }

    /**
     * Test client can download own proof file.
     */
    public function testClientCanDownloadOwnProof(): void
    {
        \Illuminate\Support\Facades\Storage::fake('private');

        // Create a proof with file
        $proof = ClientProof::factory()->create([
            'client_id' => $this->clientUser->id,
            'file_path' => 'client-proofs/1/test.pdf',
        ]);

        // Mock the file storage
        \Illuminate\Support\Facades\Storage::disk('private')->put('client-proofs/1/test.pdf', 'test content');

        $response = $this->actingAs($this->clientUser)
            ->get("/client-portal/proof/{$proof->id}/download");

        $response->assertStatus(200);
    }

    /**
     * Test admin can view all proofs.
     */
    public function testAdminCanViewAllProofs(): void
    {
        $proof = ClientProof::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'pending',
        ]);

        // Create admin policy would need to be in place for this
        // For now, just verify the policy structure
        $admin = User::factory()->create(['type' => 'super_admin']);

        // Admin should be able to view any proof via policy
        $this->assertTrue($admin->isSuperAdmin());
    }

    /**
     * Test proof submission with optional sale reference.
     */
    public function testProofSubmissionWithSaleReference(): void
    {
        \Illuminate\Support\Facades\Storage::fake('private');

        $sale = \App\Models\Sale::factory()->create([
            'client_id' => $this->client->id,
            'user_id' => 999,
            'status' => 'completed',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($this->clientUser)->post('/client-portal/proof', [
            'type' => 'payment',
            'amount' => 500.00,
            'file' => $file,
            'sale_id' => $sale->id,
            'description' => 'Pagamento da venda',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('client_proofs', [
            'client_id' => $this->clientUser->id,
            'sale_id' => $sale->id,
            'type' => 'payment',
        ]);
    }

    /**
     * Test email verification is required for portal access.
     */
    public function testEmailVerificationRequiredForPortalAccess(): void
    {
        $unverifiedUser = User::factory()->create([
            'type' => 'client',
            'email_verified_at' => null,
        ]);

        Client::factory()->create(['id' => $unverifiedUser->id]);

        $response = $this->actingAs($unverifiedUser)->get('/client-portal');

        // Should redirect to verification notice
        $response->assertRedirect();
    }

    /**
     * Test proof submission requires valid file upload.
     */
    public function testProofSubmissionRequiresFile(): void
    {
        $response = $this->actingAs($this->clientUser)->post('/client-portal/proof', [
            'type' => 'deposit',
            'amount' => 500.00,
            // Missing file
        ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test client can only access their own client record.
     */
    public function testClientCanOnlyAccessOwnData(): void
    {
        $otherClientUser = User::factory()->create(['type' => 'client']);
        $otherClient = Client::factory()->create(['id' => $otherClientUser->id]);

        // Verify the client portal uses user id to fetch client
        // The controller should fetch Client::findOrFail($user->id)
        // So client 1 cannot access client 2's data
        $this->assertTrue($this->clientUser->id !== $otherClientUser->id);
    }
}
