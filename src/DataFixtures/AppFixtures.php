<?php
namespace App\DataFixtures;

use App\Entity\Admin;
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
        // Create Admin
        $admin = new Admin();
        $admin->setUsername('john_doe');
        $admin->setRoles([]);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'zaq1@WSX')
        );
        $admin->setAccountCreated(new \DateTime('2025-02-16 19:38:14'));
        $admin->setActive(true);

        $manager->persist($admin);

        // Create Section
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

        // Create Settings
        $settingsData = [
            ['browserTabLogo', 'Logo widoczne w zakładce przeglądarki.', null, 'string', true, true],
            ['headerLogo', 'Główne logo widoczne w nagłówku.', null, 'string', true, true],
            ['browserTabMainPageTitle', 'Tytuł strony głównej na karcie przeglądarki.', 'artek.com.pl', 'string', true, true],
            ['companyEmailAddress', 'Email kontaktowy firmy widoczny w stopce strony.', 'biuro@artek.com.pl', 'string', true, true],
            ['companyPhoneNumber', 'Numer kontaktowy firmy widoczny w stopce strony.', '(+48) 000 000 000', 'string', true, true],
            ['companyAddress', 'Adres siedziby firmy widoczny w stopce strony.', 'NMikołaja Kopernika 1, 872-122 Bydgoszcz', 'string', true, true],
            ['browserTabGalleryTitle', 'Tytuł karty galerii w przeglądarce.', 'Galeria', 'string', true, true],
            ['galleryHeader', 'Nagłówek galerii.', 'Galeria', 'string', true, true],
            ['galleryDescription', 'Opis galerii.', 'Oto zdjęcia naszych wyrobów.', 'string', true, true],
            ['isGalleryActive', 'Czy strona galerii jest aktywna.', '1', 'bool', true, true],
            ['browserTabFormTitle', 'Tytuł karty formularza kontaktowego w przeglądarce.', 'Formularz kontaktowy', 'string', true, true],
            ['formHeader', 'Nagłówek formularza.', 'Skontaktuj się z nami!', 'string', true, true],
            ['formDescription', 'Opis formularza.', 'Zwykle odpowiadamy w ciągu jednego dnia roboczego.', 'string', true, true],
            ['isFormActive', 'Czy strona formularza jest aktywna.', '1', 'bool', true, true],
            ['formAddress', 'Adres, na który będą przychodzić wiadomości z formularza kontaktowego.', 'mail@mail.com', 'string', true, true],
            ['banner', 'Zdjęcie banerowe strony głównej.', null, 'string', true, true],
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

        $manager->flush();
    }
}