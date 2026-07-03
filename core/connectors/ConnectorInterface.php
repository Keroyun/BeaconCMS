<?php
declare(strict_types=1);

/**
 * Interface for all form connectors.
 * Connectors process form submissions and send data to third-party services.
 */
interface ConnectorInterface
{
    /**
     * Return the unique identifier for the connector (e.g., 'zendesk', 'email')
     */
    public function getId(): string;

    /**
     * Return the human-readable name of the connector
     */
    public function getName(): string;

    /**
     * Return an array of configuration fields required by the connector.
     * Format: ['field_key' => ['type' => 'text', 'label' => 'Field Label', 'required' => true]]
     */
    public function getConfigFields(): array;

    /**
     * Execute the connector logic when a form is submitted.
     * 
     * @param array $entryData The submitted form data (key-value pairs)
     * @param array $config The connector configuration settings for this specific form
     * @param array $form The form database record
     * @return bool True on success, False on failure
     */
    public function execute(array $entryData, array $config, array $form): bool;
}
