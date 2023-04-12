# Sodium Encrypted Input

This field acts like a normal text input, but the value stored in the database is encrypted and is therefore not human-readable. This is useful for storing data such as passwords, oAuth/API tokens or personal details. Encryption is achieved using Sodium with a key generated on extension installation.

This means that if your database is somehow compromised then your content remains safe. A hacker would need to also obtain the key from the config file to decrypt your content.

**¡Note!** Cannot be used to upgrade from the former `Encrypted Input field`, since the data encrypted by `mcrypt` cannot be decrypted by `sodium`.

Minimum required PHP version is `7.2`.


## Usage

1. Put the `sodium_encrypted_input` folder into your `/extensions` directory
2. Enable "Field: Encrypted Input" from the System > Extensions page
3. Update the salt on the System > Preferences page
4. Add "Sodium Encrypted Input" fields to your sections


## Key

This extension generates a key for you on extension installation. If you uninstall the extension or change the key in the config file, the values can not be decrypted again!


## Credits

Many thanks to [Michael Hay](http://korelogic.co.uk) for funding the [original extension](https://github.com/symphonists/encrypted_input) that most of this code is based on and permitting it to be released as open source. And of course [Nick Dunn](http://nick-dunn.co.uk) for creating the initial version of this extension, plus [Nicholas Brassard](https://github.com/nitriques) for further work on it.