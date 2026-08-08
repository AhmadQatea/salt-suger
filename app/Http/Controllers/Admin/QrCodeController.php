<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSetting;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class QrCodeController extends Controller
{
    public function __construct(
        protected QrCodeService $qrCodes,
    ) {}

    public function index(): View
    {
        $settings = RestaurantSetting::cached();
        $menuUrl = $this->qrCodes->menuUrl();
        $qrSvg = null;
        $error = null;

        try {
            $qrSvg = $this->qrCodes->svgMarkup($menuUrl);
        } catch (Throwable $exception) {
            report($exception);
            $error = 'تعذر إنشاء رمز QR حالياً.';
        }

        return view('admin.qr-code.index', [
            'menuUrl' => $menuUrl,
            'qrSvg' => $qrSvg,
            'qrError' => $error,
            'supportsPng' => $this->qrCodes->supportsPng(),
            'restaurantName' => $settings?->restaurant_name ?: config('app.name', 'Salt&Suger'),
        ]);
    }

    public function download(string $format): Response|RedirectResponse
    {
        $format = strtolower($format);

        if (! $this->qrCodes->isSupportedFormat($format)) {
            abort(404);
        }

        try {
            $contents = $this->qrCodes->generate($format);
        } catch (InvalidArgumentException) {
            abort(404);
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()
                ->route('admin.qr-code.index')
                ->withErrors(['qr' => 'تعذر إنشاء رمز QR حالياً.']);
        }

        return response($contents, 200, [
            'Content-Type' => $this->qrCodes->mimeType($format),
            'Content-Disposition' => 'attachment; filename="'.$this->qrCodes->downloadFilename($format).'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
