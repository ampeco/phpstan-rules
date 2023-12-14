## How to Use

1. Require `ampeco/phpstan-rules` as a composer dependency in your project
2. In your `phpstan.neon` file, add the following line to the `includes` section:
```
- ./vendor/ampeco/phpstan-rules/rules.neon
```
3. In the parameters section, configure the directories by adding the `preventNamespacesUsageInDirectories` parameter. For instance:
```
preventNamespacesUsageInDirectories:
    directories:
        - modules:
            - App
```

In this example, importing anything from the "App" namespace in the modules directory is forbidden.

You can define multiple rules as shown below:
```
preventNamespacesUsageInDirectories:
    directories:
        - modules:
            - App
        - app/Csv/Resources:
            - Ampeco\Modules
```

Here, it is forbidden to import anything from the `App` namespace in the `modules` directory. Additionally, importing anything from `Ampeco\Modules` to the files in the `app/Csv/Resources` directory is also prohibited.