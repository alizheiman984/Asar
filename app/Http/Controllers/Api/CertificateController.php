<?php


namespace App\Http\Controllers\Api;
use TCPDF;
use App\Models\Volunteer;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CertificateController extends Controller
{
    public function generateCertificate(Request $request)
    {
        $volunteer = Volunteer::where('id', auth()->user()->id)->firstOrFail();

        if ($volunteer->total_points < 10) {
            return response()->json([
                'message' => 'لا يمكنك إصدار شهادة لأن لديك أقل من 100 نقطة.',
            ], 400); 
        }

        $certificateDir = public_path('certificates');
        if (!file_exists($certificateDir)) {
            mkdir($certificateDir, 0777, true); 
        }

        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetFont('dejavusans', '', 14); 

       
        $pdf->AddPage();

        $pdf->SetFont('dejavusans', '', 16);
        $pdf->SetTextColor(0, 0, 0); 
        $pdf->SetXY(20, 40);
        $pdf->MultiCell(0, 10, "شهادة تقدير\n\nتم منح هذه الشهادة للمتطوع: " . $volunteer->full_name . "\n\nلإتمامه أكثر من 100 نقطة ");

    
        $campaigns = $volunteer->campaigns; 
        $campaignNames = $campaigns->pluck('campaign_name')->implode(', ');

       
        $pdf->SetXY(20, 70); 
        $pdf->MultiCell(0, 10, "المتطوع شارك في الحملات التالية: " . $campaignNames);

        $sealImage = public_path('images/image0_large.jpg');
        $pdf->Image($sealImage, 150, 150, 40, 40, '', '', '', false, 300, '', false, false, 0, false, false, false);

        $pdf->SetFont('dejavusans', 'I', 12);
        $pdf->SetXY(20, 190);
        $pdf->MultiCell(0, 10, "التاريخ: " . now()->toDateString(), 0, 'C', 0, 1);

      
        $certificatePath = 'certificates/volunteer_' . $volunteer->id . '.pdf';
        $pdf->Output(public_path($certificatePath), 'F'); 

     
        Certificate::create([
            'volunteer_id' => $volunteer->id,
            'level' => 'متطوع نشط',
            'points_threshold' => 100,
            'certificate_type' => 'تشجيعية',
            'created_at' => now(),
            'certificate_path' => $certificatePath,
            'certificate_issued' => true,
        ]);

        return response()->json([
            'message' => 'تم إصدار الشهادة بنجاح',
            'certificate_path' => $certificatePath,
        ]);
    }



}
