<?php
namespace Drupal\test\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Class NodeSerializer.
 *
 * @package Drupal\test\Controller
 */
class NodeSerializer extends ControllerBase {
    /**
     * The Serializer.
     *
     * @var \Symfony\Component\Serializer\SerializerInterface
     *   Serializer Dependency will be stored inside this.
     */
    protected $serializer;
    /**
     * The config factory service.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;
    /**
     * Entyity manager
     *
     * @var Drupal\node\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;
    /**
     * NodeSerializer constructor.
     *
     * @param \Symfony\Component\Serializer\SerializerInterface $serializerInterface
     *   Dependency.
     */
    public function __construct(SerializerInterface $serializerInterface, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
        $this->serializer = $serializerInterface;
        $this->configFactory = $configFactory;
        $this->entityTypeManager = $entityTypeManager;
    }
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static ($container->get('serializer'), $container->get('config.factory'), $container->get('entity_type.manager'));
    }
    /**
     * Access page parametres ,checks for correct api key and returns JSON data
     *
     */
    public function getPageParams(Request $request) {
        $site_api_key = $request->get('siteapikey');
        $node_id = $request->get('nodeid');
        if ($site_api_key == $this->configFactory->get('siteapikey.configuration')->get('siteapikey')) {
            $node = $this->entityTypeManager->getStorage('node')->load($node_id);
            if ($node->getType() != 'page') {
                drupal_set_message("Access denied ");
                $build = ['#markup' => 'Access Denied'];
            } else {
                $serializedNode = $this->serializer->serialize($node, 'json');
                // Build Output.
                $build = ['#type' => 'markup', '#markup' => $serializedNode, ];
            }
        } else {
            drupal_set_message("Incorrect api key ");
            $build = ['#markup' => 'Incorrect api key'];
        }
        return $build;
    }
}
