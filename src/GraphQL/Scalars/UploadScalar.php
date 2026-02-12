<?php

namespace CdnServices\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Illuminate\Http\UploadedFile;

/**
 * GraphQL Upload scalar (multipart/form-data ile gelen dosyayı temsil eder).
 * Client tarafında GraphQL multipart request spec kullanıldığında
 * değişken olarak UploadedFile örneği gelir.
 */
class UploadScalar extends ScalarType
{
    public $name = 'Upload';
    public $description = 'Yüklenecek dosya (multipart request)';

    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }
        return $value;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        return null;
    }
}
