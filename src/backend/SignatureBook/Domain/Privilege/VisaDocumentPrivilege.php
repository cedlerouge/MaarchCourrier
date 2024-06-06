<?php

namespace MaarchCourrier\SignatureBook\Domain\Privilege;

use MaarchCourrier\Core\Domain\Authorization\Port\PrivilegeInterface;

class VisaDocumentPrivilege implements PrivilegeInterface
{
    public function getName(): string
    {
        return 'visa_documents';
    }
}
