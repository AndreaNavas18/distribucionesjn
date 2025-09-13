<?php
use GuzzleHttp\Client;

function subirPDFaSupabase($pdfPath, $pdfFileName) {
    $supabaseUrl = $_ENV['SUPABASE_URL'];
    $supabaseAnonKey = $_ENV['SUPABASE_ANON_KEY'];
    $bucketName = 'pdfs';

    if (!file_exists($pdfPath)) {
        error_log("❌ El archivo PDF no existe: $pdfPath");
        return null;
    }

    $client = new Client();

    try {
        $bodyContent = file_get_contents($pdfPath);
        $response = $client->request('POST', "$supabaseUrl/storage/v1/object/$bucketName/$pdfFileName", [
            'headers' => [
                'apikey' => $supabaseAnonKey,
                'Authorization' => "Bearer $supabaseAnonKey",
                'Content-Type' => 'application/pdf',
                'x-upsert' => 'true'
            ],
            'body' => $bodyContent
        ]);

        if (in_array($response->getStatusCode(), [200, 201])) {
            return "$supabaseUrl/storage/v1/object/public/$bucketName/$pdfFileName";
        }

        error_log("❌ Error subiendo PDF a Supabase: " . $response->getStatusCode());
        return null;
    } catch (\Exception $e) {
        error_log("Error subiendo PDF a Supabase: " . $e->getMessage());
        return null;
    }
}
