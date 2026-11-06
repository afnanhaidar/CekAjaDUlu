<?php

use App\Controllers\AttendanceController;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AttendanceUploadTest extends CIUnitTestCase
{
    public function testDefinesCodeIgniterRulesForPhotoUpload(): void
    {
        $controller = new TestableAttendanceUploadController(null, null);
        $rules = $controller->rules();

        $this->assertSame([
            'uploaded[surat]',
            'is_image[surat]',
            'mime_in[surat,image/jpg,image/jpeg,image/png]',
            'max_size[surat,2048]',
        ], $rules['surat']['rules']);
    }

    public function testRejectsFileThatIsNotAnImage(): void
    {
        $response = (new TestableAttendanceUploadController(
            'Foto surat harus berupa gambar.',
            null
        ))->upload();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Foto surat harus berupa gambar.', session()->getFlashdata('error'));
    }

    public function testRejectsFileLargerThanTwoMegabytes(): void
    {
        $response = (new TestableAttendanceUploadController(
            'Ukuran foto surat maksimal 2 MB.',
            null
        ))->upload();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Ukuran foto surat maksimal 2 MB.', session()->getFlashdata('error'));
    }

    public function testAcceptsValidJpegUpload(): void
    {
        $this->assertValidImageIsUploaded('surat.jpg');
    }

    public function testAcceptsValidPngUpload(): void
    {
        $this->assertValidImageIsUploaded('surat.png');
    }

    private function assertValidImageIsUploaded(string $filename): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('getRandomName')->willReturn($filename);
        $file->expects($this->once())
            ->method('move')
            ->with(WRITEPATH . 'uploads/surat/', $filename);

        $response = (new TestableAttendanceUploadController(null, $file))->upload();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Foto berhasil diunggah', session()->getFlashdata('success'));
    }
}

final class TestableAttendanceUploadController extends AttendanceController
{
    public function __construct(
        private readonly ?string $uploadValidationError,
        private readonly ?UploadedFile $file
    ) {
    }

    /**
     * @return array<string, array{label: string, rules: list<string>, errors: array<string, string>}>
     */
    public function rules(): array
    {
        return $this->photoValidationRules();
    }

    protected function photoUploadValidationError(): ?string
    {
        return $this->uploadValidationError;
    }

    protected function uploadedPhoto(): ?UploadedFile
    {
        return $this->file;
    }
}
