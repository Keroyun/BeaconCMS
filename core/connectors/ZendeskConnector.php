<?php
declare(strict_types=1);

require_once __DIR__ . '/ConnectorInterface.php';

/**
 * Zendesk Connector
 * Creates a Zendesk support ticket from a form submission.
 */
class ZendeskConnector implements ConnectorInterface
{
    public function getId(): string
    {
        return 'zendesk';
    }

    public function getName(): string
    {
        return 'Zendesk Ticket Integration';
    }

    public function getConfigFields(): array
    {
        return [
            'subdomain' => [
                'type' => 'text',
                'label' => 'Zendesk Subdomain (e.g. yourcompany)',
                'required' => true,
            ],
            'email' => [
                'type' => 'email',
                'label' => 'Admin Email',
                'required' => true,
            ],
            'api_token' => [
                'type' => 'password',
                'label' => 'API Token',
                'required' => true,
            ],
            'subject_mapping' => [
                'type' => 'text',
                'label' => 'Subject (Use {field_name} for variables)',
                'required' => false,
            ]
        ];
    }

    public function execute(array $entryData, array $config, array $form): bool
    {
        $subdomain = $config['subdomain'] ?? '';
        $email = $config['email'] ?? '';
        $apiToken = $config['api_token'] ?? '';

        if (empty($subdomain) || empty($email) || empty($apiToken)) {
            error_log("Zendesk Connector: Missing configuration.");
            return false;
        }

        $url = "https://{$subdomain}.zendesk.com/api/v2/tickets.json";
        
        // Find email and name fields from entry data
        $requesterEmail = $entryData['email'] ?? $entryData['e-mail'] ?? 'noreply@example.com';
        $requesterName = $entryData['name'] ?? $entryData['full_name'] ?? 'Form Submitter';

        // Build the ticket description
        $description = "New form submission from: " . $form['title'] . "\n\n";
        foreach ($entryData as $key => $value) {
            $description .= ucfirst(str_replace(['_', '-'], ' ', $key)) . ": " . $value . "\n";
        }

        // Build subject
        $subject = $config['subject_mapping'] ?? "Form Submission: {$form['title']}";
        if (!empty($config['subject_mapping'])) {
            // Replace {field_name} with actual data
            foreach ($entryData as $key => $value) {
                $subject = str_replace('{' . $key . '}', $value, $subject);
            }
        }

        $ticketData = [
            'ticket' => [
                'subject' => $subject,
                'comment' => ['body' => $description],
                'requester' => [
                    'name' => $requesterName,
                    'email' => $requesterEmail,
                ],
                'tags' => ['beaconcms_form', 'form_' . $form['id']]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $email . "/token:" . $apiToken);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ticketData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Important in local dev, but generally should be true in prod
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("Zendesk Connector Error ($httpCode): " . $response);
            return false;
        }
    }
}
