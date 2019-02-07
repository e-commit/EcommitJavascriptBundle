<?php
/**
 * This file is part of the EcommitJavascriptBundle package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\JavascriptBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToArrayTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class JqueryAutocompleteEntityAjaxType extends AbstractType
{
    use EntityNormalizerTrait;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor
     *
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry, RouterInterface $router)
    {
        $this->registry = $registry;
        $this->router = $router;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key', HiddenType::class);
        $builder->add('text', TextType::class);

        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntityToIdTransformer(
                        $options['query_builder'],
                        $options['identifier'],
                        false
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntityToArrayTransformer(
                $options['query_builder'],
                $options['identifier'],
                $options['choice_label']
            )
        );
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['url'] = $options['url'];
        $view->vars['image_autocomplete'] = $options['image_autocomplete'];
        $view->vars['image_ok'] = $options['image_ok'];
        $view->vars['min_chars'] = $options['min_chars'];
    }


    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'image_autocomplete' => 'bundles/ecommitjavascript/images/i16/keyboard_magnify.png',
                'image_ok' => 'bundles/ecommitjavascript/images/i16/apply.png',
                'error_bubbling' => false,
            )
        );

        $this->addCommonDefaultOptions($resolver, $this->registry, $this->router);
    }

    public function getBlockPrefix()
    {
        return 'ecommit_javascript_jqueryautocompleteentityajax';
    }
}
