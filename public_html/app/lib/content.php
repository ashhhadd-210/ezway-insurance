<?php
defined('APP') or exit;

/**
 * Everything EZ Way offers. Used by the Home and Services pages, the quote
 * form (service picker + "details" questions), validation, emails and the
 * dashboard, so a service only needs to be described once.
 *
 * 'flow' => 'auto' runs the drivers/vehicles/coverage steps.
 * 'flow' => 'details' asks the 'fields' questions instead.
 * Field types: radio (pick one), checks (pick any), select, text, textarea, vin.
 * 'show_if' => [key, [values]] only asks a field when an earlier answer matches.
 */
function services(): array
{
    $notes = ['key' => 'notes', 'type' => 'textarea', 'label' => L('Anything else we should know?', '¿Algo más que debamos saber?'), 'optional' => true,
        'placeholder' => L('Questions, deadlines, best time to call…', 'Preguntas, fechas límite, mejor hora para llamarte…')];

    return [
        'personal_auto' => [
            'icon'  => 'car',
            'flow'  => 'auto',
            'title' => L('Personal auto', 'Auto personal'),
            'short' => L('Cars, trucks and SUVs for you and your family. Add every driver and vehicle in one quote.', 'Autos, trocas y SUVs para ti y tu familia. Agrega todos los conductores y vehículos en una sola cotización.'),
            'page'  => 'auto-insurance',
        ],
        'commercial_auto' => [
            'icon'  => 'truck',
            'flow'  => 'auto',
            'title' => L('Commercial auto', 'Auto comercial'),
            'short' => L('Work trucks, vans and business vehicles with higher limits, insured under your company name.', 'Trocas de trabajo, vans y vehículos de negocio con límites más altos, a nombre de tu compañía.'),
            'page'  => 'auto-insurance#commercial',
        ],
        'home' => [
            'icon'  => 'home',
            'flow'  => 'details',
            'title' => L('Home & renters insurance', 'Seguro de casa e inquilinos'),
            'short' => L('Protect your home, mobile home, rental or RV, and everything inside it.', 'Protege tu casa, casa móvil, renta o RV, y todo lo que hay adentro.'),
            'page'  => 'services#home',
            'items' => [
                ['home', L('Homeowners', 'Dueños de casa')],
                ['key', L('Renters', 'Inquilinos')],
                ['truck', L('Mobile & manufactured homes', 'Casas móviles y manufacturadas')],
                ['car', L('RVs & motorhomes', 'RVs y casas rodantes')],
            ],
            'fields' => [
                ['key' => 'coverage_for', 'type' => 'radio', 'label' => L('What do you want to insure?', '¿Qué quieres asegurar?'), 'options' => [
                    'own'    => L('My home (I own it)', 'Mi casa (soy dueño)'),
                    'renter' => L('Renters insurance', 'Seguro de inquilino'),
                    'rv'     => L('RV / motorhome', 'RV / casa rodante'),
                ]],
                // Homeowners (house, mobile home, condo)
                ['key' => 'property_type', 'type' => 'radio', 'label' => L('Type of home', 'Tipo de casa'), 'show_if' => ['coverage_for', ['own']], 'options' => [
                    'house'  => L('House', 'Casa'),
                    'mobile' => L('Mobile / manufactured home', 'Casa móvil / manufacturada'),
                    'condo'  => L('Condo / townhome', 'Condominio / townhome'),
                ]],
                ['key' => 'year_built', 'type' => 'text', 'label' => L('Year built', 'Año de construcción'), 'optional' => true, 'show_if' => ['coverage_for', ['own']],
                    'placeholder' => '2005', 'maxlength' => 4, 'inputmode' => 'numeric', 'pattern' => '/^(18|19|20)\d{2}$/'],
                ['key' => 'roof_year', 'type' => 'text', 'label' => L('Year the roof was last replaced', 'Año en que se cambió el techo'), 'optional' => true, 'show_if' => ['coverage_for', ['own']],
                    'placeholder' => '2018', 'maxlength' => 4, 'inputmode' => 'numeric', 'pattern' => '/^(19|20)\d{2}$/',
                    'hint' => L('Not sure? Leave it blank.', '¿No sabes? Déjalo en blanco.')],
                ['key' => 'claims', 'type' => 'radio', 'label' => L('Any home insurance claims in the last 5 years?', '¿Algún reclamo de seguro de casa en los últimos 5 años?'), 'show_if' => ['coverage_for', ['own']], 'options' => [
                    'no' => L('No', 'No'), 'yes' => L('Yes', 'Sí'),
                ]],
                ['key' => 'claims_info', 'type' => 'textarea', 'label' => L('Tell us about the claims', 'Cuéntanos de los reclamos'), 'optional' => true, 'show_if' => ['claims', ['yes']],
                    'placeholder' => L('What happened, the year and the amount paid, if you know it', 'Qué pasó, el año y cuánto pagaron, si lo sabes')],
                // Renters
                ['key' => 'additional_insured', 'type' => 'radio', 'label' => L('Add an additional insured?', '¿Agregar un asegurado adicional?'), 'show_if' => ['coverage_for', ['renter']],
                    'hint' => L('For example your landlord, property manager or a roommate.', 'Por ejemplo tu landlord, administrador o compañero de casa.'), 'options' => [
                    'no' => L('No', 'No'), 'yes' => L('Yes', 'Sí'),
                ]],
                ['key' => 'additional_insured_name', 'type' => 'text', 'label' => L('Additional insured name', 'Nombre del asegurado adicional'), 'show_if' => ['additional_insured', ['yes']], 'maxlength' => 120,
                    'placeholder' => L('Name or company', 'Nombre o compañía')],
                // RV
                ['key' => 'rv_vin', 'type' => 'vin', 'label' => L('RV VIN', 'VIN del RV'), 'show_if' => ['coverage_for', ['rv']],
                    'hint' => L('That\'s all we need for your RV.', 'Es todo lo que necesitamos de tu RV.')],
                $notes,
            ],
        ],
        'business' => [
            'icon'  => 'briefcase',
            'flow'  => 'details',
            'title' => L('Business & contractors', 'Negocios y contratistas'),
            'short' => L('General liability, workers comp, builders risk, equipment and more for contractors and small businesses.', 'Responsabilidad general, compensación de trabajadores, riesgos de construcción, equipo y más para contratistas y negocios.'),
            'page'  => 'services#business',
            'items' => [
                ['shield', L('General liability', 'Responsabilidad general')],
                ['hardhat', L('Workers compensation', 'Compensación de trabajadores')],
                ['building', L('Builders risk', 'Riesgos de construcción')],
                ['tool', L('Equipment', 'Equipo')],
                ['seal', L('Fidelity bonds', 'Bonos de fidelidad')],
                ['truck', L('Commercial vehicles', 'Vehículos comerciales')],
            ],
            // Business name and trade are asked on the "You" step.
            'fields' => [
                ['key' => 'coverages', 'type' => 'checks', 'label' => L('What coverage do you need?', '¿Qué cobertura necesitas?'), 'options' => [
                    'general_liability' => L('General liability', 'Responsabilidad general'),
                    'workers_comp'      => L('Workers compensation', 'Compensación de trabajadores'),
                    'commercial_auto'   => L('Commercial vehicle', 'Vehículo comercial'),
                    'builders_risk'     => L('Builders risk', 'Riesgos de construcción'),
                    'equipment'         => L('Equipment', 'Equipo'),
                    'fidelity_bond'     => L('Fidelity bond', 'Bono de fidelidad'),
                    'not_sure'          => L('Not sure, help me', 'No sé, ayúdame'),
                ]],
                ['key' => 'payroll', 'type' => 'text', 'label' => L('Yearly payroll', 'Nómina anual'), 'optional' => true, 'maxlength' => 30, 'inputmode' => 'decimal',
                    'placeholder' => L('e.g. $80,000', 'Ej. $80,000'), 'hint' => L('How much you pay your employees in a year.', 'Cuánto pagas a tus empleados en un año.')],
                ['key' => 'gross_income', 'type' => 'text', 'label' => L('Gross income (or expected)', 'Ingresos brutos (o esperados)'), 'optional' => true, 'maxlength' => 30, 'inputmode' => 'decimal',
                    'placeholder' => L('e.g. $250,000', 'Ej. $250,000'), 'hint' => L('Your yearly sales. An estimate is fine.', 'Tus ventas al año. Un estimado está bien.')],
                ['key' => 'employees', 'type' => 'select', 'label' => L('Number of employees', 'Número de empleados'), 'optional' => true, 'options' => [
                    'just_me' => L('Just me', 'Solo yo'), '2_5' => '2 – 5', '6_20' => '6 – 20', '21_plus' => '21+',
                ]],
                ['key' => 'years', 'type' => 'select', 'label' => L('Years in business', 'Años en el negocio'), 'optional' => true, 'options' => [
                    'new' => L('Just starting', 'Apenas empezando'), '1_2' => L('1 – 2 years', '1 – 2 años'), '3_plus' => L('3+ years', '3+ años'),
                ]],
                $notes,
            ],
        ],
        'bonds' => [
            'icon'  => 'seal',
            'flow'  => 'details',
            'title' => L('Bonds', 'Bonos'),
            'short' => L('Title, permit, fidelity, contractor and license bonds, issued fast.', 'Bonos de título, de permiso, de fidelidad, de contratista y de licencia, rápido.'),
            'page'  => 'services#bonds',
            'items' => [
                ['doc', L('Title bonds', 'Bonos de título')],
                ['id', L('Permit bonds', 'Bonos de permiso')],
                ['seal', L('Fidelity bonds', 'Bonos de fidelidad')],
                ['hardhat', L('Contractor & license bonds', 'Bonos de contratista y licencia')],
            ],
            'fields' => [
                ['key' => 'bond_type', 'type' => 'radio', 'label' => L('Which bond do you need?', '¿Qué bono necesitas?'), 'options' => [
                    'title'      => L('Title bond', 'Bono de título'),
                    'permit'     => L('Permit bond', 'Bono de permiso'),
                    'fidelity'   => L('Fidelity bond', 'Bono de fidelidad'),
                    'contractor' => L('Contractor or license bond', 'Bono de contratista o licencia'),
                    'other'      => L('Other / not sure', 'Otro / no sé'),
                ]],
                ['key' => 'bond_amount', 'type' => 'text', 'label' => L('Bond amount you need', 'Monto del bono que necesitas'), 'optional' => true, 'maxlength' => 30, 'inputmode' => 'decimal',
                    'show_if' => ['bond_type', ['title', 'permit', 'fidelity', 'contractor', 'other']],
                    'placeholder' => L('e.g. $10,000', 'Ej. $10,000'), 'hint' => L('Not sure? Leave it blank and we\'ll help you.', '¿No sabes? Déjalo en blanco y te ayudamos.')],
                ['key' => 'notes', 'type' => 'textarea', 'label' => L('What is the bond for?', '¿Para qué es el bono?'), 'optional' => true,
                    'placeholder' => L('Vehicle, permit, business license, contract…', 'Vehículo, permiso, licencia de negocio, contrato…')],
            ],
        ],
        'title' => [
            'icon'  => 'plate',
            'flow'  => 'details',
            'title' => L('Title & registration', 'Títulos, placas y registro'),
            'short' => L('Title transfers, plates, registration, temporary plates, salvage and out-of-state titles.', 'Transferencias de título, placas, registro, placas temporales, títulos salvage y de otro estado.'),
            'page'  => 'services#title',
            'items' => [
                ['doc', L('Title transfers', 'Transferencia de título')],
                ['plate', L('License plates', 'Placas')],
                ['id', L('Registration', 'Registro')],
                ['clock', L('Temporary plates', 'Placas temporales')],
                ['refresh', L('Renewals', 'Renovaciones')],
                ['alert', L('Salvage titles', 'Títulos salvage')],
                ['map', L('Out-of-state titles', 'Títulos de otro estado')],
                ['seal', L('Title bonds', 'Bonos de título')],
            ],
            'fields' => [
                ['key' => 'needs', 'type' => 'radio', 'label' => L('What do you need?', '¿Qué necesitas?'), 'options' => [
                    'transfer' => L('Title transfer', 'Transferencia de título'),
                    'other'    => L('Something else', 'Otra cosa'),
                ]],
                ['key' => 'other_need', 'type' => 'textarea', 'label' => L('Tell us what you need', 'Cuéntanos qué necesitas'), 'show_if' => ['needs', ['other']],
                    'placeholder' => L('Plates, registration, renewal, salvage or out-of-state title…', 'Placas, registro, renovación, título salvage o de otro estado…')],
                ['key' => 'vin', 'type' => 'vin', 'label' => L('Vehicle VIN', 'VIN del vehículo'), 'optional' => true,
                    'hint' => L('Optional. Helps us get your paperwork ready.', 'Opcional. Nos ayuda a preparar tus papeles.')],
            ],
        ],
        'llc' => [
            'icon'  => 'building',
            'flow'  => 'details',
            'title' => L('LLC registration', 'Registro de LLC'),
            'short' => L('Register your LLC and get your business ready to work.', 'Registra tu LLC y deja tu negocio listo para trabajar.'),
            'page'  => 'services#llc',
            'items' => [
                ['building', L('LLC registration', 'Registro de LLC')],
                ['shield', L('Business insurance once you\'re registered', 'Seguro para tu negocio ya registrado')],
            ],
            'fields' => [
                ['key' => 'business_name', 'type' => 'text', 'label' => L('Business name you want', 'Nombre que quieres para tu negocio'), 'optional' => true, 'maxlength' => 120],
                $notes,
            ],
        ],
    ];
}

function service(string $key): ?array
{
    return services()[$key] ?? null;
}

/** Auto coverages explained on the Auto insurance page. */
function auto_coverages(): array
{
    return [
        'liability' => [
            'icon'  => 'scale',
            'title' => L('Liability', 'Responsabilidad civil'),
            'short' => L('Required in Texas. Pays for injuries and damage you cause to other people.', 'Obligatorio en Texas. Paga las lesiones y daños que causes a otras personas.'),
            'long'  => L('If you cause an accident, liability pays for the other driver\'s medical bills and the repairs to their car or property, up to your limits. It also helps with legal costs if you are sued.', 'Si causas un accidente, la responsabilidad civil paga los gastos médicos del otro conductor y la reparación de su auto o propiedad, hasta tus límites. También ayuda con gastos legales si te demandan.'),
            'good'  => [L('Every driver: it is required by Texas law', 'Todos los conductores: lo exige la ley de Texas'), L('Anyone with savings or a home to protect', 'Quien tenga ahorros o una casa que proteger')],
            'tag'   => L('Required', 'Obligatorio'),
        ],
        'full' => [
            'icon'  => 'shield-plus',
            'title' => L('Full coverage', 'Cobertura completa'),
            'short' => L('Comprehensive and collision together, so your own car is protected too.', 'Comprensivo y colisión juntos, para que tu propio auto también esté protegido.'),
            'long'  => L('Collision pays to fix your car after an accident, no matter who is at fault. Comprehensive covers theft, vandalism, hail, flood, fire and hitting an animal. If your car is financed or leased, your lender (lienholder) usually requires it.', 'Colisión paga la reparación de tu auto después de un accidente, sin importar quién tuvo la culpa. Comprensivo cubre robo, vandalismo, granizo, inundación, incendio y choques con animales. Si tu auto está financiado, tu financiera (lienholder) normalmente lo exige.'),
            'good'  => [L('Financed or leased cars', 'Autos financiados o en leasing'), L('Newer vehicles you couldn\'t easily replace', 'Vehículos nuevos difíciles de reemplazar')],
            'tag'   => L('Per vehicle', 'Por vehículo'),
        ],
        'pip' => [
            'icon'  => 'heart',
            'title' => L('Personal injury protection', 'Protección contra lesiones (PIP)'),
            'short' => L('Medical bills and lost wages for you and your passengers, no matter who caused the accident.', 'Gastos médicos y salario perdido para ti y tus pasajeros, sin importar quién causó el accidente.'),
            'long'  => L('PIP pays medical expenses and part of lost income for you and your passengers after an accident, regardless of fault. In Texas it is included unless you reject it in writing.', 'PIP paga gastos médicos y parte del salario perdido para ti y tus pasajeros después de un accidente, sin importar la culpa. En Texas se incluye a menos que lo rechaces por escrito.'),
            'good'  => [L('Drivers who carry passengers or family', 'Quien lleva pasajeros o familia'), L('Anyone with a high health-insurance deductible', 'Quien tiene un deducible alto de seguro médico')],
            'tag'   => L('Whole policy', 'Toda la póliza'),
        ],
        'um' => [
            'icon'  => 'umbrella',
            'title' => L('Uninsured motorist', 'Conductor sin seguro'),
            'short' => L('Protects you when the other driver has no insurance, or not enough.', 'Te protege cuando el otro conductor no tiene seguro, o no le alcanza.'),
            'long'  => L('Many drivers on Texas roads have no insurance. Uninsured/underinsured motorist coverage pays for your injuries and car repairs when the at-fault driver can\'t pay, including many hit-and-runs.', 'Muchos conductores en Texas no tienen seguro. La cobertura de conductor sin seguro paga tus lesiones y la reparación de tu auto cuando el culpable no puede pagar, incluyendo muchos casos de choque y fuga.'),
            'good'  => [L('Everyone: it costs little for what it covers', 'Todos: cuesta poco para lo que cubre'), L('Daily commuters on busy highways', 'Quien maneja diario por autopistas')],
            'tag'   => L('Whole policy', 'Toda la póliza'),
        ],
        'roadside' => [
            'icon'  => 'wrench',
            'title' => L('Roadside assistance', 'Asistencia en el camino'),
            'short' => L('Towing, jump-starts, flat tires, lockouts and fuel delivery, 24/7.', 'Grúa, paso de corriente, llantas ponchadas, llaves dentro del auto y gasolina, 24/7.'),
            'long'  => L('If your car breaks down, roadside assistance sends help: towing, battery jump-starts, flat-tire changes, lockout service and fuel delivery, usually for a few dollars a month.', 'Si tu auto se descompone, la asistencia en el camino te manda ayuda: grúa, paso de corriente, cambio de llanta, apertura de puertas y gasolina, normalmente por unos cuantos dólares al mes.'),
            'good'  => [L('Commuters and long-distance drivers', 'Quien maneja largas distancias'), L('Older vehicles and new drivers', 'Vehículos viejos y conductores nuevos')],
            'tag'   => L('Per vehicle', 'Por vehículo'),
        ],
        'rental' => [
            'icon'  => 'key',
            'title' => L('Rental reimbursement', 'Auto de renta'),
            'short' => L('Pays for a rental car while yours is in the shop after a covered claim.', 'Paga un auto de renta mientras el tuyo está en el taller después de un reclamo cubierto.'),
            'long'  => L('After a covered accident, rental reimbursement pays for a rental car or rideshare up to a daily limit while your car is repaired, so you are never stuck without a ride.', 'Después de un accidente cubierto, paga un auto de renta o viajes de rideshare hasta un límite diario mientras reparan tu auto, para que nunca te quedes sin transporte.'),
            'good'  => [L('Households with only one car', 'Familias con un solo auto'), L('Anyone who needs a car for work', 'Quien necesita el auto para trabajar')],
            'tag'   => L('Per vehicle', 'Por vehículo'),
        ],
    ];
}
