<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Http;

class StudentSyncService
{
    private string $token = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6InhJWmpEd2VtMlVMRGQyUzJSb3d4UmJ1R3FCeWhWVDZvX3Z4b3IxZ2s0QUkiLCJhbGciOiJSUzI1NiIsIng1dCI6IlBjWDk4R1g0MjBUMVg2c0JEa3poUW1xZ3dNVSIsImtpZCI6IlBjWDk4R1g0MjBUMVg2c0JEa3poUW1xZ3dNVSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9lZDFmYzU3Zi04YTk3LTQ3ZTctOWRlMS05MzAyZGZkNzg2YWUvIiwiaWF0IjoxNzY3NjkyNzQ1LCJuYmYiOjE3Njc2OTI3NDUsImV4cCI6MTc2Nzc3OTQ0NSwiYWNjdCI6MCwiYWNyIjoiMSIsImFjcnMiOlsicDEiLCJ1cm46dXNlcjpyZWdpc3RlcnNlY3VyaXR5aW5mbyJdLCJhaW8iOiJBV1FBbS84YUFBQUFwS2Nvc0V5TmMyZVFkUW9CRFNoUHdzaklYUlJqK0ZsS0hnVFl0ZHk2Sm5Nc2x5c2I0OFV3TEc1Tmh4Yjhna2QwTTdOTmU0Y2NZTTJVWFBSc3VDL3NVRUptdEUyb01UMElYWlpqcHdFMnpwRUhkbzNERHhESEEwZUs1bWZ2aVRWRSIsImFtciI6WyJwd2QiLCJtZmEiXSwiYXBwX2Rpc3BsYXluYW1lIjoiR3JhcGggRXhwbG9yZXIiLCJhcHBpZCI6ImRlOGJjOGI1LWQ5ZjktNDhiMS1hOGFkLWI3NDhkYTcyNTA2NCIsImFwcGlkYWNyIjoiMCIsImNhcG9saWRzX2xhdGViaW5kIjpbImU5YmQxMzIzLWRmNWUtNDFiZC04NDY0LTlkY2U3MjZkYWEzYyIsIjhmZTNjZWM1LWNlYjYtNDQ0Ni1iNjU4LWIzODNmNTU2MWYyOCJdLCJmYW1pbHlfbmFtZSI6IktuYWVwcyIsImdpdmVuX25hbWUiOiJMb3dpZSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjE5My4xOTEuMTgyLjEyOSIsIm5hbWUiOiJLbmFlcHMgTG93aWUiLCJvaWQiOiJiODkzNzZjNi04YTg1LTQ4MjAtYWExMi0yNzlhNjExNDM4ZGMiLCJvbnByZW1fc2lkIjoiUy0xLTUtMjEtMTAwMTQ4Nzk1MS00Njc0NjE5OTItMzA4MjM5OTM0OS0xNTU5MzUiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDIxRjJFOTU3OSIsInJoIjoiMS5BUUlBZjhVZjdaZUs1MGVkNFpNQzM5ZUdyZ01BQUFBQUFBQUF3QUFBQUFBQUFBRGNBQU1DQUEuIiwic2NwIjoiQXVkaXRMb2cuUmVhZC5BbGwgQ2FsZW5kYXJzLlJlYWRXcml0ZSBDYWxlbmRhcnMuUmVhZFdyaXRlLlNoYXJlZCBDb250YWN0cy5SZWFkV3JpdGUgRGV2aWNlTWFuYWdlbWVudEFwcHMuUmVhZC5BbGwgRGV2aWNlTWFuYWdlbWVudEFwcHMuUmVhZFdyaXRlLkFsbCBEZXZpY2VNYW5hZ2VtZW50Q29uZmlndXJhdGlvbi5SZWFkLkFsbCBEZXZpY2VNYW5hZ2VtZW50Q29uZmlndXJhdGlvbi5SZWFkV3JpdGUuQWxsIERldmljZU1hbmFnZW1lbnRNYW5hZ2VkRGV2aWNlcy5Qcml2aWxlZ2VkT3BlcmF0aW9ucy5BbGwgRGV2aWNlTWFuYWdlbWVudE1hbmFnZWREZXZpY2VzLlJlYWQuQWxsIERldmljZU1hbmFnZW1lbnRNYW5hZ2VkRGV2aWNlcy5SZWFkV3JpdGUuQWxsIERldmljZU1hbmFnZW1lbnRSQkFDLlJlYWQuQWxsIERldmljZU1hbmFnZW1lbnRSQkFDLlJlYWRXcml0ZS5BbGwgRGV2aWNlTWFuYWdlbWVudFNlcnZpY2VDb25maWcuUmVhZC5BbGwgRGV2aWNlTWFuYWdlbWVudFNlcnZpY2VDb25maWcuUmVhZFdyaXRlLkFsbCBEaXJlY3RvcnkuQWNjZXNzQXNVc2VyLkFsbCBEaXJlY3RvcnkuUmVhZFdyaXRlLkFsbCBGaWxlcy5SZWFkV3JpdGUuQWxsIEdyb3VwLlJlYWRXcml0ZS5BbGwgSWRlbnRpdHlSaXNrRXZlbnQuUmVhZC5BbGwgSWRlbnRpdHlSaXNreVVzZXIuUmVhZC5BbGwgSWRlbnRpdHlSaXNreVVzZXIuUmVhZFdyaXRlLkFsbCBNYWlsLlJlYWRXcml0ZSBNYWlsYm94U2V0dGluZ3MuUmVhZCBNYWlsYm94U2V0dGluZ3MuUmVhZFdyaXRlIE5vdGVzLlJlYWRXcml0ZS5BbGwgb3BlbmlkIFBlb3BsZS5SZWFkIFByZXNlbmNlLlJlYWQgUHJlc2VuY2UuUmVhZC5BbGwgcHJvZmlsZSBSZXBvcnRzLlJlYWQuQWxsIFNpdGVzLkZ1bGxDb250cm9sLkFsbCBTaXRlcy5SZWFkV3JpdGUuQWxsIFRhc2tzLlJlYWRXcml0ZSBVc2VyLlJlYWQgVXNlci5SZWFkQmFzaWMuQWxsIFVzZXIuUmVhZFdyaXRlIFVzZXIuUmVhZFdyaXRlLkFsbCBVc2VyQXV0aGVudGljYXRpb25NZXRob2QuUmVhZCBlbWFpbCIsInNpZCI6IjAwYjhkMDk5LTVmMGQtNjY2OC1iNWY4LWNiOWMzZDVmMzY5YyIsInNpZ25pbl9zdGF0ZSI6WyJpbmtub3dubnR3ayIsImttc2kiXSwic3ViIjoiOVhtbXQ3dGR0VmxINFlFQlhoT2dqTzVXMnE0UlNRZThJQ2JJeTRVLXVVZyIsInRlbmFudF9yZWdpb25fc2NvcGUiOiJFVSIsInRpZCI6ImVkMWZjNTdmLThhOTctNDdlNy05ZGUxLTkzMDJkZmQ3ODZhZSIsInVuaXF1ZV9uYW1lIjoibG93aWUua25hZXBzQHN0dWRlbnQua2RnLmJlIiwidXBuIjoibG93aWUua25hZXBzQHN0dWRlbnQua2RnLmJlIiwidXRpIjoiQU9oYm50N1RTay1HODV2N0FnVE9BUSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19hY2QiOjE0NzQ2NjQwNzMsInhtc19hY3RfZmN0IjoiNyAzIiwieG1zX2NjIjpbIkNQMSJdLCJ4bXNfZnRkIjoiTzhFNG9BZEZyVFZ4WUlEZDBXdXdrZmNmLWM4eUotTS0tcVpPbWpUREJsWUJaWFZ5YjNCbGJtOXlkR2d0WkhOdGN3IiwieG1zX2lkcmVsIjoiMSAxMCIsInhtc19zc20iOiIxIiwieG1zX3N0Ijp7InN1YiI6InUwYUp0Z2IxYWYwV1ZFZm9mWFpMYXBsc2lHZ3dkVGh2c3NUR0pLblhzLTgifSwieG1zX3N1Yl9mY3QiOiIzIDEwIiwieG1zX3RjZHQiOjEzNjM5ODI3NzEsInhtc190ZGJyIjoiRVUiLCJ4bXNfdG50X2ZjdCI6IjMgMTIifQ.PzkA7UmQuKXnTl3aiPu6ChmSLfj0JmS7ycEWEc_oWb1oo1MfNy1dk38aEfA293ywflnq1u1Hx9wDlALzI1YeL_V4dWTQ21UtoXMutaySdp_HoCaNR2uX5TqKyBIXJO264yM8YgSeWKsY38JArrrpSOKm4QeTRV3soFI0MK6EPzjQDJusF5yaqvzWzvjGN_HYrqs8tPPy-ZpcOEMj4CYn3a5GBgZpZxbl9ZIxrmmGC5Ta4y5EsyoNt3gZH12EPpKI5AS9vuPFR_55stDb0Q9HW8PMDXyPRB-zxcQq291lpKw7NXSgyJhMGeuoxNJY3BDmjt0MByYJgJVqcM-T100HRw';

    private const STUDENT_GROUP_ID = '0fac3712-df91-4224-8e0f-fd17556dcbae';

    public function syncAllStudents(): int
    {
        $url = sprintf(
            'https://graph.microsoft.com/beta/groups/%s/transitiveMembers/microsoft.graph.user',
            self::STUDENT_GROUP_ID
        );

        $total   = 0;
        $nextUrl = $url;

        while ($nextUrl) {
            $res = Http::withToken($this->token)
                ->get($nextUrl, [
                    '$select' => 'displayName,mail,userPrincipalName,onPremisesExtensionAttributes',
                    '$top'    => 999,
                ])
                ->throw()
                ->json();

            foreach ($res['value'] ?? [] as $user) {
                if (
                    data_get($user, 'onPremisesExtensionAttributes.extensionAttribute1')
                    !== 'Student'
                ) {
                    continue;
                }

                $cardUid = data_get(
                    $user,
                    'onPremisesExtensionAttributes.extensionAttribute9'
                );

                if (! $cardUid) {
                    continue;
                }

                Student::updateOrCreate(
                    ['external_id' => $cardUid],
                    [
                        'name' => $user['displayName']
                            ?? $user['mail']
                            ?? $user['userPrincipalName']
                            ?? 'Onbekend',

                        'group' => data_get(
                            $user,
                            'onPremisesExtensionAttributes.extensionAttribute2'
                        ),
                        'card_uid' => $cardUid,
                    ]
                );

                $total++;
            }

            $nextUrl = $res['@odata.nextLink'] ?? null;
        }

        return $total;
    }

    /**
     * Helper voor ESP UID
     */
    public function cardUidToEsp(string|int $decimalUid): string
    {
        $hex   = strtoupper(str_pad(dechex((int) $decimalUid), 8, '0', STR_PAD_LEFT));
        $bytes = array_reverse(str_split($hex, 2));

        return implode('', $bytes);
    }
}
