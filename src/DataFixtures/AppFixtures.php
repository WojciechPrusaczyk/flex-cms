<?php
namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Colors;
use App\Entity\DashboardSettings;
use App\Entity\Section;
use App\Entity\Sections;
use App\Entity\Setting;
use App\Entity\Settings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setUsername('john_doe');
        $admin->setRoles([]);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'zaq1@WSX')
        );
        $admin->setAccountCreated(new \DateTime('2025-02-16 19:38:14'));
        $admin->setActive(true);

        $manager->persist($admin);

        $section = new Sections();
        $section->setName('Example Section');
        $section->setActive(true);
        $section->setAddedBy($admin);
        $section->setWide(false);
        $section->setIsTitleVisible(true);
        $section->setPosition(1);
        $section->setStartBeingActive(new \DateTime());
        $section->setStopBeingActive((new \DateTime())->modify('+1 year'));

        $manager->persist($section);

        $settingsData = [
            ['browserTabLogo', 'Logo widoczne w zakładce przeglądarki.', null, 'file', true, true],
            ['headerLogo', 'Główne logo widoczne w nagłówku.', null, 'file', true, true],
            ['browserTabMainPageTitle', 'Tytuł strony głównej na karcie przeglądarki.', 'artek.com.pl', 'string', true, true],
            ['companyEmailAddress', 'Email kontaktowy firmy widoczny w stopce strony.', 'biuro@artek.com.pl', 'string', true, true],
            ['companyPhoneNumber', 'Numer kontaktowy firmy widoczny w stopce strony.', '(+48) 000 000 000', 'string', true, true],
            ['companyAddress', 'Adres siedziby firmy widoczny w stopce strony.', 'Mikołaja Kopernika 1, 72-122 Bydgoszcz', 'string', true, true],
            ['browserTabGalleryTitle', 'Tytuł karty galerii w przeglądarce.', 'Galeria', 'string', true, true],
            ['galleryHeader', 'Nagłówek galerii.', 'Galeria', 'string', true, true],
            ['galleryDescription', 'Opis galerii.', 'Oto zdjęcia naszych wyrobów.', 'string', true, true],
            ['isGalleryActive', 'Czy strona galerii jest aktywna.', '1', 'boolean', true, true],
            ['browserTabFormTitle', 'Tytuł karty formularza kontaktowego w przeglądarce.', 'Formularz kontaktowy', 'string', true, true],
            ['formHeader', 'Nagłówek formularza.', 'Skontaktuj się z nami!', 'string', true, true],
            ['formDescription', 'Opis formularza.', 'Zwykle odpowiadamy w ciągu jednego dnia roboczego.', 'string', true, true],
            ['isFormActive', 'Czy strona formularza jest aktywna.', '1', 'boolean', true, true],
            ['formAddress', 'Adres, na który będą przychodzić wiadomości z formularza kontaktowego.', 'mail@mail.com', 'string', true, true],
            ['banner', 'Zdjęcie banerowe strony głównej.', null, 'file', true, true],
            ['bannerText', 'Tytuł widoczny na banerze strony głównej.', 'Meble i wyroby z drewna na zamówienie', 'string', true, true],
        ];

        foreach ($settingsData as [$name, $description, $value, $type, $isEditable, $isPublic]) {
            $setting = new Settings();
            $setting->setName($name);
            $setting->setDescription($description);
            $setting->setValue($value);
            $setting->setType($type);
            $setting->setEditable($isEditable);
            $setting->setPublic($isPublic);

            $manager->persist($setting);
        }

        $categoriesData = [
            ['Sekcje', 'sections', 'sections.svg', true],
            ['Galeria', 'gallery', 'gallery.svg', true],
            ['Kolory', 'colors', 'colors.svg', true],
            ['Style', 'stylesheets', 'stylesheets.svg', true],
            ['Ustawienia', 'settings', 'settings.svg', true],
            ['Skrypty', 'scripts', 'scripts.svg', true],
        ];

        foreach ($categoriesData as [$name, $englishName, $iconFileName, $isActive]) {
            $category = new DashboardSettings();
            $category->setName($name);
            $category->setEnglishName($englishName);
            $category->setIconFileName($iconFileName);
            $category->setIsActive($isActive);
            $manager->persist($category);
        }

        $colors = [
            ['name' => 'mainBackground', 'description' => 'Kolor głównego tła dokumentu.', 'value' => 'rgba(255,255,255,1)', 'type' => 'rgba'],
            ['name' => 'mainBackgroundMargin', 'description' => 'Kolor marginesów strony głównej.', 'value' => 'rgba(238,255,238,1)', 'type' => 'rgba'],
            ['name' => 'logoBackgroundPrimary', 'description' => 'Główny kolor tła loga.', 'value' => 'rgba(255,255,255,1)', 'type' => 'rgba'],
            ['name' => 'logoBackgroundSecondary', 'description' => 'Główny kolor gradientu loga.', 'value' => 'rgba(216,250,215,1)', 'type' => 'rgba'],
            ['name' => 'logoBackgroundTertiary', 'description' => 'Dodatkowy kolor gradientu loga.', 'value' => 'rgba(220,253,219,1)', 'type' => 'rgba'],
            ['name' => 'headerPrimary', 'description' => 'Główny kolor nagłówka.', 'value' => 'rgba(255,255,255,1)', 'type' => 'rgba'],
            ['name' => 'buttonBackground', 'description' => 'Główny kolor przycisków.', 'value' => 'rgba(208,248,207,0.81)', 'type' => 'rgba'],
            ['name' => 'buttonText', 'description' => 'Kolor tekstu przycisków.', 'value' => 'rgba(0,0,0,1)', 'type' => 'rgba'],
            ['name' => 'buttonBorder', 'description' => 'Kolor obramowania przycisków.', 'value' => 'rgba(207,233,207,0.88)', 'type' => 'rgba'],
            ['name' => 'footerPrimary', 'description' => 'Główny kolor stopki.', 'value' => 'rgba(233,210,153,1)', 'type' => 'rgba'],
            ['name' => 'footerSecondary', 'description' => 'Dodatkowy kolor stopki.', 'value' => 'rgba(219,194,131,1)', 'type' => 'rgba'],
            ['name' => 'footerTextPrimary', 'description' => 'Główny kolor tekstu stopki', 'value' => 'rgba(0,0,0,1)', 'type' => 'rgba'],
            ['name' => 'footerTextSecondary', 'description' => 'Dodatkowy kolor tekstu stopki', 'value' => 'rgba(0,0,0,1)', 'type' => 'rgba'],
            ['name' => 'footerCreatorText', 'description' => 'Główny kolor podpisu twórcy strony.', 'value' => 'rgba(35,33,29,1)', 'type' => 'rgba'],
            ['name' => 'bannerTextColor', 'description' => 'Kolor napisu na banerze strony głównej.', 'value' => 'rgba(255,255,255,1)', 'type' => 'rgba']
        ];

        foreach ($colors as $colorData) {
            $color = new Colors();
            $color->setName($colorData['name']);
            $color->setDescription($colorData['description']);
            $color->setValue($colorData['value']);
            $color->setType($colorData['type']);

            $manager->persist($color);
        }

        $manager->flush();
    }
}