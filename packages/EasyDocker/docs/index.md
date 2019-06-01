<div align="center">
    <h1>LoyaltyCorp - EasyDocker</h1>
    <p>CLI tool to generate Docker files.</p>
</div>

---

# Install package

The recommended way to install this package is to use [Composer][1].

```bash
$ composer require loyaltycorp/easy-docker
```

# Generate the Docker files

Once the package installed using [Composer][1], your vendor directory will should contain the binary file to use
to generate the Docker files. Simply run the `generate` command, and that's it!

```bash
$ vendor/bin/easy-docker generate
```

# Output

The `generate` command will ask you to provide the required information about your project to make sure to generate
the files that fits the most your needs. Once done, it will generate a `easy-docker-manifest.json` file to keep track
of the files generated and the version of the command used to generate them.

[1]: https://getcomposer.org/
