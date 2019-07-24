<?php
namespace BD\EzPlatformQueryFieldType\Controller;

use BD\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Server\Values\ContentList;
use eZ\Publish\Core\REST\Server\Values\RestContent;

class QueryFieldRestController
{
    /**
     * @var \BD\EzPlatformQueryFieldType\API\QueryFieldService
     */
    private $queryFieldService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(
        QueryFieldService $queryFieldService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService
    )
    {
        $this->queryFieldService = $queryFieldService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
    }

    public function getResults($contentId, $versionNumber, $fieldDefinitionIdentifier): ContentList
    {
        $content = $this->contentService->loadContent($contentId, null, $versionNumber);

        return new ContentList(
            array_map(
                function(Content $content) {
                    return new RestContent(
                        $content->contentInfo,
                        $this->locationService->loadLocation($content->contentInfo->mainLocationId),
                        $content,
                        $this->getContentType($content->contentInfo),
                        $this->contentService->loadRelations($content->getVersionInfo())
                    );
                },
                $this->queryFieldService->loadFieldData($content, $fieldDefinitionIdentifier)
            ),
        );
    }

    private function getContentType(ContentInfo $contentInfo): ContentType
    {
        static $contentTypes = [];

        if (!isset($contentTypes[$contentInfo->contentTypeId])) {
            $contentTypes[$contentInfo->contentTypeId] = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        }

        return $contentTypes[$contentInfo->contentTypeId];
    }
}