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

use Doctrine\Persistence\ManagerRegistry;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToIdsTransformer;
use Ecommit\JavascriptBundle\Form\DataTransformer\Entity\EntitiesToJsonTransformer;
use Ecommit\JavascriptBundle\Form\EventListener\FixMultiAutocomplete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class TokenInputEntitiesAjaxType extends AbstractType
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
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry, RouterInterface $router)
    {
        $this->registry = $registry;
        $this->router = $router;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['input'] == 'key') {
            $builder->addModelTransformer(
                new ReversedTransformer(
                    new EntitiesToIdsTransformer(
                        $options['query_builder'],
                        $options['identifier'],
                        $options['max']
                    )
                )
            );
        }

        $builder->addViewTransformer(
            new EntitiesToJsonTransformer(
                $options['query_builder'],
                $options['identifier'],
                $options['choice_label'],
                'id',
                'name',
                $options['max'],
                true,
                ',',
                true
            )
        );

        //Remove prePopulate if client's value is incorrect
        $builder->addEventSubscriber(new FixMultiAutocomplete());
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['url'] = $options['url'];
        $view->vars['hint_text'] = $options['hint_text'];
        $view->vars['no_results_text'] = $options['no_results_text'];
        $view->vars['searching_text'] = $options['searching_text'];
        $view->vars['theme'] = $options['theme'];
        $view->vars['min_chars'] = $options['min_chars'];
        $view->vars['max'] = $options['max'];
        $view->vars['prevent_duplicates'] = $options['prevent_duplicates'] ? 'true' : 'false';
        $view->vars['query_param'] = $options['query_param'];
    }


    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'hint_text' => 'Type in a search term',
                'no_results_text' => 'No results',
                'searching_text' => 'Searching',
                'theme' => null,
                'max' => 50,
                'prevent_duplicates' => true,
                'query_param' => 'term',
                //Field not required because the "html 5 error" is displayed
                //outside the screen (field outside the screen): Browser error is invisible
                'required' => false,
                'compound' => false,
            )
        );

        $this->addCommonDefaultOptions($resolver, $this->registry, $this->router);
    }

    public function getBlockPrefix()
    {
        return 'ecommit_javascript_tokeninputentitiesajax';
    }
}
