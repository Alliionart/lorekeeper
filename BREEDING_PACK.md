# Alliion's Breeding Extension Guide
Welcome to the Breeding Extension guide! This will give you a good idea of where to start when setting up your own breeding system. This extension is NOT drop in ready. It will require you having enough developer knowledge to adjust the code to your preferences.

> As a note for this pack - I will NOT offer bug fixes (if not directly related to the system) or customizations. This is a pack meant for you to work off of yourself. It does 80% of the annoying work for you. You code the roller, you make the modifiers, you adjust any settings.

## Extensions Used
These are extensions as part of this pack to assist with the breeding system. If you have bugs with those extensions please consult the authors unless you believe it is due to my implementation.

- Design Hub - By Alliion
- Breeding Permissions (modified) - By Mercury
- Character Lineages - By DeeP-ci

## Installation
1. Pull and merge any conflicts. This is not for beginners, this pack is a 3-in-1.
2. Run `php artisan migrate`
3. Run `php artisan update-site-settings`
4. Run `php artisan add-breeding-permission-currency` (Skip if you already have this extension installed)
5. Run `php artisan update-extension-tracker`

This should create all of your needed tables and updates! As a note depending on your breeding system I would HIGHLY recommend for you to create migrations as needed.

> Not included in this extension is an update to the site_settings table. You will almost certainly need to change the value column type to longtext for this to work.

## Configurations
Let's get started! The first step to modifying this guide is in your breeding.php config file. This file is 100% for you to modify. I've left some items in here I used for World of Reos so you can see how it works. This is great for having a constant category to check for, such as traits, if you want to see about using it for your own modifiers implementation feel free.

It is intended for this file to be bare bones.

## Admin Settings
Before you ever set up your breeding settings, a few things must be done:
1. You must have a species added
2. You must have a minimum of 1 subspecies
3. You must have configured traits
4. You must have configured markings
5. (Highly Recommended) You have created an item category for breeding items

If you have that information, you are probably ready to explore the admin settings. Head to **Admin > Settings > Breeding Settings**.

On this screen you will find many breeding settings. You can configure the following:
- Litter Sizes per species
- Breeding rates
  - Species x Species
  - Subtype x Subtype
  - Trait Category + Rarity x Rarity
  - Marking Rates (via Design Hub)
  - Mutations (Traits that will appear randomly on a geno)
- Modifiers (Items that can update rates, or other data, whatever you want to implement)
- Inbreeding traits (category id must be added in the config - or remove it)

Take some time to fill this out and ensure you fix any errors.

## The Bread & Butter: The Roller
This is the most exciting part! It's time to take a look at that fancy roller, head on over to the **BreedingRollerService.php**. This is your holy grail for configuring how your automated roller will work. I've already done a lot of groundwork for you, it's now up to you to modify it to your needs! The general roller works like this:
1. BreedingService.php calls the roller service attaching the breeding. You can choose to set modifiers BEFORE the `$roller->roll()` happens.
2. The roll function will first set the modifiers up from the breeding, get the litter size, (optionally) check the genders of the parents, and then it is time to roll!
3. Take a look next at the `rollPup()` function. This is the setup for the current pup. It will check the species/subtype first and then move onto checking each individual parameter. You may add *whatever* you want here. But keep in mind, anything you add here must also be added in the Breeding.php model for rendering the pup!
4. Each of the functions within the rollPup() may be edited to your specific logic. I highly encourage throwing a `dd()` in the rollPup or roll function as you are testing to see how this works. I've also left a debug log that is inactive you may use to your advantage to test how your rates are rolling.
5. Take your time with this, I find that going one section at a time helps.

## Breeding Submission Form
The breeding submission form uses quite a bit of AJAX. Any additional checks you would like to add, you should pass through the **BreedingController.php** (not admin) to be able to use for the javascript. I won't be going over that as there is so much possibility. But please use any pre-existing code as reference, or don't!

The breeding form may be admin-only, open or closed (configured in the normal site settings).

When submitting a breeding:
1. Player chooses where to pull the character from
  - Their own character - Characters are pulled from the current user.
  - Breeding permission - You guessed it, from a breeding permission the current user owns
  - Starter token - These are item tags. Create a "token" item and assign characters from then admin account to it. If a user has that token, they can use any assigned character.
2. Player selects their breeding type. Default is Full and Split. Splits only refer to "shared" breedings.
3. Player selects the items to be used in the breeding. These are modifiers YOU need to set up in the code. Because they can be so variable you will need to setup how they work in the roller, and how they are pulled into this breeding form and the breeding settings. Feel free to implement additional logic to disable them.

The player then submits the breeding. Wait - but there is no user inventory, how does it pull the items? Glad you asked, when creating the breeding the system automatically finds the relevant item so long as it is available and attaches the stack. Neat!


## That About Wraps it Up
This is a high-level overview of this extension pack. If you have questions, I am more than happy to answer and offer advice. Otherwise I hope this has given you a decent start to your breeding journey!

### Ideas to Implement for Your System
- Automate breeding entirely (Easy)
- Implement fun modifiers (Moderate)
- Implement a timer for breedings (Easy)


