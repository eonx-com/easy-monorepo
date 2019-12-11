<div align="center">
    <h1>EonX - EasyCfhighlander</h1>
    <p>CLI tool to generate Cfhighlander templates.</p>
</div>

---

# Install package

The recommended way to install this package is to use [Composer][1].

```bash
$ composer require eonx/easy-cfhighlander
```

# Available commands

This package provides you with the following commands:

- `cloudformation`: Generate the Cfhighlander files for the cloudformation repository of your project
- `code`: Generate the Cfhighlander files for the repository containing the code of your project

# Generate the Cfhighlander files

Once the package installed using [Composer][1], your vendor directory will should contain the binary file to use
to generate the Cfhighlander files:

```bash
# Cloudformation
$ vendor/bin/easy-cfhighlander cloudformation

# Code
$ vendor/bin/easy-cfhighlander code
```

# Output

The commands will ask you to provide the required information about your project to make sure to generate
the files that fits the most your needs. Once done, it will generate a `easy-cfhighlander-manifest.json` file to keep 
track of the files generated and the version of the command used to generate them.

[1]: https://getcomposer.org/
