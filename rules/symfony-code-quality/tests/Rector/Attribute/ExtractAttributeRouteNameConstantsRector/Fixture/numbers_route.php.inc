<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class NumbersRoute
{
    #[Route(path: "path", name: "100_level")]
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class NumbersRoute
{
    #[Route(path: "path", name: \App\ValueObject\Routing\RouteName::ROUTE_100_LEVEL)]
    public function run()
    {
    }
}

?>
