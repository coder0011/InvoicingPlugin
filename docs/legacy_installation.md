### Legacy installation (without Symfony Flex)

1. Require plugin with composer:

    ```bash
    composer require sylius/invoicing-plugin
    ```

1. Add plugin class and other required bundles to your `config/bundles.php`:

    ```php
    $bundles = [
        Knp\Bundle\SnappyBundle\KnpSnappyBundle::class => ['all' => true],
        Sylius\InvoicingPlugin\SyliusInvoicingPlugin::class => ['all' => true],
    ];
    ```

1. Import configuration:

    ```yaml
    imports:
        - { resource: '@SyliusInvoicingPlugin/config/config.yaml' }
    ```

1. Import routes:

    ````yaml
   sylius_refund:
       resource: "@SyliusInvoicingPlugin/config/routes.yaml"
    ````

1. Check if you have `wkhtmltopdf` binary. If not, you can download it [here](https://wkhtmltopdf.org/downloads.html).

    In case `wkhtmltopdf` is not located in `/usr/local/bin/wkhtmltopdf`, add a following snippet at the end of your application's `config.yml`:
    
    ```yaml
    knp_snappy:
        pdf:
            enabled: true
            binary: /usr/local/bin/wkhtmltopdf # Change this! :)
            options: []
    ```   

1. Apply migrations to your database:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

1. If you want to generate invoices for orders placed before plugin's installation run the following command using your terminal:

   ```bash
   bin/console sylius-invoicing:generate-invoices
   ```

1. Clear cache:

    ```bash
    bin/console cache:clear
    ```
