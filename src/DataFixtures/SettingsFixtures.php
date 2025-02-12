<?php

namespace App\DataFixtures;

use App\Entity\Settings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SettingsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $settings = [
            ['browserTabLogo', 'Logo widoczne w zakładce przeglądarki.', null, 'string', true, true],
            ['headerLogo', 'Główne logo widoczne w nagłówku.', null, 'string', true, true],
            ['browserTabMainPageTitle', 'Tytuł strony głównej na karcie przeglądarki.', null, 'string', true, true],
            ['companyEmailAddress', 'Email kontaktowy firmy widoczny w stopce strony.', 'biuro@artek.com.pl', 'string', true, true],
            ['companyPhoneNumber', 'Numer kontaktowy firmy widoczny w stopce strony.', '(+48) 791 721 060', 'string', true, true],
            ['companyAddress', 'Adres siedziby firmy widoczny w stopce strony.', 'Nowe Krąplewice 58A, 86-131 Jeżewo', 'string', true, true],
            ['browserTabGalleryTitle', 'Tytuł karty galerii w przeglądarce.', 'Galeria', 'string', true, true],
            ['galleryHeader', 'Nagłówek galerii.', null, 'string', true, true],
            ['galleryDescription', 'Opis galerii.', 'Oto zdjęcia naszych wyrobów.', 'string', true, true],
            ['isGalleryActive', 'Czy strona galerii jest aktywna.', '1', 'boolean', true, true],
            ['browserTabFormTitle', 'Tytuł karty formularza kontaktowego w przeglądarce.', 'Formularz kontaktowy', 'string', true, true],
            ['formHeader', 'Nagłówek formularza.', 'Skontaktuj się z nami!', 'string', true, true],
            ['formDescription', 'Opis formularza.', 'Zwykle odpowiadamy w ciągu jednego dnia roboczego.', 'string', true, true],
            ['isFormActive', 'Czy strona formularza jest aktywna.', '1', 'boolean', true, true],
            ['formAddress', 'Adres, na który będą przychodzić wiadomości z formularza kontaktowego.', 'djartek@o2.pl', 'string', true, true],
            ['banner', 'Zdjęcie banerowe strony głównej.', null, 'string', true, true],
            ['bannerText', 'Tytuł widoczny na banerze strony głównej.', 'Meble i wyroby z drewna na zamówienie', 'string', true, true],
        ];

        foreach ($settings as [$name, $description, $value, $type, $isEditable, $isPublic]) {
            $setting = new Settings();
            $setting->setName($name);
            $setting->setDescription($description);
            $setting->setValue($value);
            $setting->setType($type);
            $setting->setEditable($isEditable);
            $setting->setPublic($isPublic);
            $manager->persist($setting);
        }

        $manager->flush();
    }
}